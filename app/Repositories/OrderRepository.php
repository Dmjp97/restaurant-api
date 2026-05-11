<?php

namespace App\Repositories;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use CodeIgniter\Database\BaseConnection;

/**
 * OrderRepository
 *
 * All order-related DB queries are centralised here.
 * The service layer never touches the query builder directly.
 */
class OrderRepository implements OrderRepositoryInterface
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function findById(int $id): ?array
    {
        $order = $this->db->table('orders o')
            ->select('o.*, u.name as customer_name, t.name as tenant_name')
            ->join('users u',   'u.id = o.user_id',   'left')
            ->join('tenants t', 't.id = o.tenant_id', 'left')
            ->where('o.id', $id)
            ->get()->getRowArray();

        if (!$order) {
            return null;
        }

        $order['items'] = $this->db->table('order_items oi')
            ->select('oi.*, p.name as product_name, p.sku')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where('oi.order_id', $id)
            ->get()->getResultArray();

        return $order;
    }

    public function findByTenant(int $tenantId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        $builder = $this->db->table('orders o')
            ->select('o.id, o.status, o.total, o.created_at, u.name as customer_name')
            ->join('users u', 'u.id = o.user_id', 'left')
            ->where('o.tenant_id', $tenantId)
            ->orderBy('o.created_at', 'DESC');

        if (!empty($filters['status'])) {
            $builder->where('o.status', $filters['status']);
        }

        if (!empty($filters['from'])) {
            $builder->where('o.created_at >=', $filters['from'] . ' 00:00:00');
        }

        if (!empty($filters['to'])) {
            $builder->where('o.created_at <=', $filters['to'] . ' 23:59:59');
        }

        $total  = $builder->countAllResults(false);
        $orders = $builder->limit($limit, ($page - 1) * $limit)->get()->getResultArray();

        return [
            'data'       => $orders,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $limit,
            'last_page'  => (int) ceil($total / $limit),
        ];
    }

    public function create(array $data): int
    {
        $this->db->transStart();

        $this->db->table('orders')->insert([
            'tenant_id'  => $data['tenant_id'],
            'user_id'    => $data['user_id'],
            'status'     => 'pending',
            'notes'      => $data['notes'] ?? null,
            'total'      => $data['total'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $orderId = $this->db->insertID();

        foreach ($data['items'] as $item) {
            $this->db->table('order_items')->insert([
                'order_id'   => $orderId,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['quantity'] * $item['unit_price'],
            ]);
        }

        // Record initial status in timeline
        $this->recordTimeline($orderId, 'pending', $data['user_id'], 'Order created');

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new \RuntimeException('Failed to create order. Transaction rolled back.');
        }

        return $orderId;
    }

    public function updateStatus(int $id, string $status, int $changedBy): bool
    {
        $this->db->table('orders')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->recordTimeline($id, $status, $changedBy);

        return $this->db->affectedRows() > 0;
    }

    public function getTimeline(int $orderId): array
    {
        return $this->db->table('order_timeline ot')
            ->select('ot.*, u.name as changed_by_name')
            ->join('users u', 'u.id = ot.changed_by', 'left')
            ->where('ot.order_id', $orderId)
            ->orderBy('ot.created_at', 'ASC')
            ->get()->getResultArray();
    }

    public function cancel(int $id, int $cancelledBy): bool
    {
        return $this->updateStatus($id, 'cancelled', $cancelledBy);
    }

    public function countByStatus(int $tenantId): array
    {
        return $this->db->table('orders')
            ->select('status, COUNT(*) as total')
            ->where('tenant_id', $tenantId)
            ->groupBy('status')
            ->get()->getResultArray();
    }

    // ──────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────

    private function recordTimeline(int $orderId, string $status, int $userId, ?string $note = null): void
    {
        $this->db->table('order_timeline')->insert([
            'order_id'   => $orderId,
            'status'     => $status,
            'changed_by' => $userId,
            'note'       => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
