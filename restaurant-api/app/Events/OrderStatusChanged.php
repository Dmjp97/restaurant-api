<?php

namespace App\Events;

/**
 * OrderStatusChanged
 *
 * Domain event fired every time an order transitions to a new status.
 * Listeners are registered in app/Config/Events.php.
 */
class OrderStatusChanged
{
    public const NAME = 'order.status_changed';

    public readonly string $occurredAt;

    public function __construct(
        public readonly int    $orderId,
        public readonly string $previousStatus,
        public readonly string $newStatus,
        string $occurredAt = '',
    ) {
        $this->occurredAt = $occurredAt !== '' ? $occurredAt : date('Y-m-d H:i:s');
    }
}
