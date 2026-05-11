<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByTenant(int $tenantId, array $filters = [], int $page = 1, int $limit = 20): array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findTopByRevenue(int $tenantId, string $from, string $to, int $limit = 10): array;
}
