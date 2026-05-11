<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use CodeIgniter\Log\Logger;

/**
 * NotifyKitchenListener
 *
 * Reacts to OrderStatusChanged events.
 * In production this would push to a WebSocket hub or a push notification service.
 * Here we log the transition and simulate a kitchen notification.
 */
class NotifyKitchenListener
{
    /** Statuses that require kitchen awareness */
    private const KITCHEN_STATUSES = ['confirmed', 'preparing', 'ready'];

    public function handle(OrderStatusChanged $event): void
    {
        log_message(
            'info',
            "[OrderStatusChanged] Order #{$event->orderId} "
            . "{$event->previousStatus} → {$event->newStatus} at {$event->occurredAt}"
        );

        if (in_array($event->newStatus, self::KITCHEN_STATUSES, true)) {
            $this->notifyKitchen($event);
        }

        if ($event->newStatus === 'delivered') {
            $this->triggerPostDeliveryActions($event);
        }
    }

    private function notifyKitchen(OrderStatusChanged $event): void
    {
        // TODO: replace with real push service (Pusher, Firebase FCM, etc.)
        log_message(
            'info',
            "[Kitchen] Order #{$event->orderId} is now '{$event->newStatus}' — kitchen notified."
        );
    }

    private function triggerPostDeliveryActions(OrderStatusChanged $event): void
    {
        // TODO: trigger customer satisfaction survey, loyalty points, etc.
        log_message(
            'info',
            "[PostDelivery] Order #{$event->orderId} delivered — post-delivery workflow queued."
        );
    }
}
