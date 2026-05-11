<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByTenant(int $tenantId, array $filters = [], int $page = 1, int $limit = 20): array;

    public function create(array $data): int;

    public function updateStatus(int $id, string $status, int $changedBy): bool;

    public function getTimeline(int $orderId): array;

    public function cancel(int $id, int $cancelledBy): bool;

    public function countByStatus(int $tenantId): array;
}
