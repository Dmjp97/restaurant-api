<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Exceptions\BusinessException;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use CodeIgniter\Events\Events;

/**
 * OrderService
 *
 * All order business rules live here.
 * Controllers receive clean results; never raw DB rows.
 */
class OrderService
{
    /** Valid status machine transitions */
    private const TRANSITIONS = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['preparing', 'cancelled'],
        'preparing'  => ['ready'],
        'ready'      => ['delivered'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    public function __construct(
        private readonly OrderRepositoryInterface   $orderRepo,
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    public function list(int $tenantId, array $filters, int $page, int $limit): array
    {
        return $this->orderRepo->findByTenant($tenantId, $filters, $page, $limit);
    }

    public function get(int $id, int $tenantId): array
    {
        $order = $this->orderRepo->findById($id);

        if (!$order) {
            throw new BusinessException('Order not found.', 404);
        }

        $this->assertTenantOwnership($order, $tenantId);

        return $order;
    }

    public function getTimeline(int $orderId, int $tenantId): array
    {
        $this->get($orderId, $tenantId); // asserts ownership
        return $this->orderRepo->getTimeline($orderId);
    }

    /**
     * Create a new order, validating products and calculating totals.
     *
     * @throws BusinessException
     */
    public function create(array $data, int $tenantId, int $userId): array
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new BusinessException('An order must have at least one item.', 422);
        }

        $items = [];
        $total = 0.0;

        foreach ($data['items'] as $item) {
            $product = $this->productRepo->findById((int) $item['product_id']);

            if (!$product || (int) $product['tenant_id'] !== $tenantId) {
                throw new BusinessException(
                    "Product ID {$item['product_id']} not found or does not belong to this tenant.",
                    422
                );
            }

            if (!(bool) $product['is_available']) {
                throw new BusinessException("Product '{$product['name']}' is currently unavailable.", 422);
            }

            $qty       = max(1, (int) $item['quantity']);
            $unitPrice = (float) $product['price'];
            $subtotal  = $qty * $unitPrice;
            $total    += $subtotal;

            $items[] = [
                'product_id' => (int) $product['id'],
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
                'subtotal'   => $subtotal,
            ];
        }

        $orderId = $this->orderRepo->create([
            'tenant_id' => $tenantId,
            'user_id'   => $userId,
            'notes'     => $data['notes'] ?? null,
            'total'     => round($total, 2),
            'items'     => $items,
        ]);

        return $this->orderRepo->findById($orderId);
    }

    /**
     * Transition order to a new status, enforcing the state machine.
     *
     * @throws BusinessException
     */
    public function updateStatus(int $id, string $newStatus, int $tenantId, int $changedBy): array
    {
        $order = $this->get($id, $tenantId); // also asserts ownership

        $this->assertValidTransition($order['status'], $newStatus);

        $this->orderRepo->updateStatus($id, $newStatus, $changedBy);

        // Dispatch domain event — listeners handle notifications, logs, etc.
        Events::trigger(
            OrderStatusChanged::NAME,
            new OrderStatusChanged($id, $order['status'], $newStatus)
        );

        return $this->orderRepo->findById($id);
    }

    public function cancel(int $id, int $tenantId, int $cancelledBy): bool
    {
        $order = $this->get($id, $tenantId);

        if (!in_array('cancelled', self::TRANSITIONS[$order['status']] ?? [], true)) {
            throw new BusinessException(
                "Cannot cancel an order with status '{$order['status']}'.",
                422
            );
        }

        return $this->orderRepo->cancel($id, $cancelledBy);
    }

    // ──────────────────────────────────────────
    //  Private guards
    // ──────────────────────────────────────────

    private function assertTenantOwnership(array $order, int $tenantId): void
    {
        // service('auth') is the request-scoped AuthUser singleton set by JWTAuthFilter
        $user = service('auth')->user();

        // superadmin can see any tenant's orders
        if ($user !== null && $user['role'] === 'superadmin') {
            return;
        }

        if ((int) $order['tenant_id'] !== $tenantId) {
            throw new BusinessException('Order does not belong to your tenant.', 403);
        }
    }

    private function assertValidTransition(string $current, string $next): void
    {
        $allowed = self::TRANSITIONS[$current] ?? [];

        if (!in_array($next, $allowed, true)) {
            $allowedStr = empty($allowed) ? 'none' : implode(', ', $allowed);
            throw new BusinessException(
                "Invalid status transition: '{$current}' → '{$next}'. Allowed next states: {$allowedStr}.",
                422
            );
        }
    }
}
