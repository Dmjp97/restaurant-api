<?php

use App\Events\OrderStatusChanged;
use App\Listeners\NotifyKitchenListener;
use CodeIgniter\Events\Events;

/*
 |─────────────────────────────────────────────────────────────────────────────
 | Event Listeners
 |─────────────────────────────────────────────────────────────────────────────
 | Register domain event listeners here.
 | The system is intentionally decoupled: services fire events; listeners react.
 */

Events::on(OrderStatusChanged::NAME, static function (OrderStatusChanged $event) {
    (new NotifyKitchenListener())->handle($event);
});
