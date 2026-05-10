<?php

namespace Tests\Unit;

use App\Events\OrderStatusChanged;
use App\Exceptions\BusinessException;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\OrderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * OrderServiceTest
 *
 * Unit tests for the order state machine and business rules.
 * All DB calls are replaced with mocks — no database required.
 */
class OrderServiceTest extends TestCase
{
    private OrderService                            $service;
    private OrderRepositoryInterface|MockObject     $orderRepo;
    private ProductRepositoryInterface|MockObject   $productRepo;

    protected function setUp(): void
    {
        $this->orderRepo   = $this->createMock(OrderRepositoryInterface::class);
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->service     = new OrderService($this->orderRepo, $this->productRepo);
    }

    // ── State machine ───────────────────────────────────────────────────────

    /** @test */
    public function it_allows_valid_status_transitions(): void
    {
        $this->orderRepo->method('findById')->willReturn($this->fakeOrder('confirmed'));
        $this->orderRepo->method('updateStatus')->willReturn(true);
        $this->orderRepo->method('findById')->willReturnOnConsecutiveCalls(
            $this->fakeOrder('confirmed'),
            $this->fakeOrder('preparing'),
        );

        $result = $this->service->updateStatus(1, 'preparing', tenantId: 1, changedBy: 2);

        $this->assertEquals('preparing', $result['status']);
    }

    /** @test */
    public function it_rejects_invalid_status_transitions(): void
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessageMatches('/Invalid status transition/');

        $this->orderRepo->method('findById')->willReturn($this->fakeOrder('delivered'));

        $this->service->updateStatus(1, 'pending', tenantId: 1, changedBy: 2);
    }

    /** @test */
    public function it_prevents_cancelling_a_delivered_order(): void
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessageMatches('/Cannot cancel/');

        $this->orderRepo->method('findById')->willReturn($this->fakeOrder('delivered'));

        $this->service->cancel(1, tenantId: 1, cancelledBy: 2);
    }

    // ── Order creation ──────────────────────────────────────────────────────

    /** @test */
    public function it_rejects_empty_orders(): void
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessageMatches('/at least one item/');

        $this->service->create(['items' => []], tenantId: 1, userId: 3);
    }

    /** @test */
    public function it_rejects_unavailable_products(): void
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessageMatches('/unavailable/');

        $this->productRepo->method('findById')->willReturn([
            'id'           => 1,
            'tenant_id'    => 1,
            'name'         => 'Sold-Out Burger',
            'price'        => 9.90,
            'is_available' => false,
        ]);

        $this->service->create(
            ['items' => [['product_id' => 1, 'quantity' => 1]]],
            tenantId: 1,
            userId:   3,
        );
    }

    /** @test */
    public function it_rejects_products_from_another_tenant(): void
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessageMatches('/does not belong to this tenant/');

        $this->productRepo->method('findById')->willReturn([
            'id'           => 99,
            'tenant_id'    => 999, // different tenant
            'name'         => 'Foreign Product',
            'price'        => 5.00,
            'is_available' => true,
        ]);

        $this->service->create(
            ['items' => [['product_id' => 99, 'quantity' => 1]]],
            tenantId: 1,
            userId:   3,
        );
    }

    /** @test */
    public function it_calculates_order_total_correctly(): void
    {
        $this->productRepo->method('findById')->willReturn([
            'id'           => 1,
            'tenant_id'    => 1,
            'name'         => 'Classic Burger',
            'price'        => 8.90,
            'is_available' => true,
        ]);

        $this->orderRepo->method('create')->willReturn(1);
        $this->orderRepo->method('findById')->willReturn(
            array_merge($this->fakeOrder('pending'), ['total' => 17.80])
        );

        $order = $this->service->create(
            ['items' => [['product_id' => 1, 'quantity' => 2]]],
            tenantId: 1,
            userId:   3,
        );

        $this->assertEquals(17.80, $order['total']);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function fakeOrder(string $status, int $tenantId = 1): array
    {
        return [
            'id'        => 1,
            'tenant_id' => $tenantId,
            'user_id'   => 3,
            'status'    => $status,
            'total'     => 22.70,
            'items'     => [],
        ];
    }
}
