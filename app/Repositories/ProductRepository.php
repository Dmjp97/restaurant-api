<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use CodeIgniter\Database\BaseConnection;

class ProductRepository implements ProductRepositoryInterface
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function findById(int $id): ?array
    {
        return $this->db->table('products')
            ->where('id', $id)
            ->get()->getRowArray() ?: null;
    }

    public function findByTenant(int $tenantId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        $builder = $this->db->table('products')
            ->where('tenant_id', $tenantId)
            ->orderBy('name', 'ASC');

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (isset($filters['is_available'])) {
            $builder->where('is_available', (int) $filters['is_available']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('name', $filters['search'])
                ->orLike('sku', $filters['search'])
                ->groupEnd();
        }

        $total    = $builder->countAllResults(false);
        $products = $builder->limit($limit, ($page - 1) * $limit)->get()->getResultArray();

        return [
            'data'      => $products,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $limit,
            'last_page' => (int) ceil($total / max(1, $limit)),
        ];
    }

    public function create(array $data): int
    {
        $this->db->table('products')->insert(array_merge($data, [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        return $this->db->insertID();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->table('products')
            ->where('id', $id)
            ->update(array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]));

        return $this->db->affectedRows() > 0;
    }

    public function delete(int $id): bool
    {
        $this->db->table('products')->where('id', $id)->delete();
        return $this->db->affectedRows() > 0;
    }

    public function findTopByRevenue(int $tenantId, string $from, string $to, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT p.id, p.name, p.sku, p.category,
                    SUM(oi.quantity) AS units_sold,
                    SUM(oi.subtotal) AS revenue
             FROM order_items oi
             INNER JOIN orders o   ON o.id  = oi.order_id
             INNER JOIN products p ON p.id  = oi.product_id
             WHERE o.tenant_id  = ?
               AND o.status     = 'delivered'
               AND o.created_at BETWEEN ? AND ?
             GROUP BY p.id, p.name, p.sku, p.category
             ORDER BY revenue DESC
             LIMIT ?",
            [$tenantId, $from . ' 00:00:00', $to . ' 23:59:59', $limit]
        )->getResultArray();
    }
}
