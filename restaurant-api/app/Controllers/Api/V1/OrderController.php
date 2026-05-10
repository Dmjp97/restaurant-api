<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\BusinessException;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\OrderService;

class OrderController extends BaseApiController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService(
            new OrderRepository(),
            new ProductRepository(),
        );
    }

    /**
     * GET /api/v1/orders
     */
    public function index()
    {
        $tenantId = $this->resolveTenantId();

        $filters = array_filter([
            'status' => $this->request->getGet('status'),
            'from'   => $this->request->getGet('from'),
            'to'     => $this->request->getGet('to'),
        ], fn($v) => $v !== null && $v !== '');

        $page  = max(1, (int) ($this->request->getGet('page')  ?? 1));
        $limit = min(100, max(1, (int) ($this->request->getGet('limit') ?? 20)));

        return $this->paginated($this->orderService->list($tenantId, $filters, $page, $limit));
    }

    /**
     * GET /api/v1/orders/:id
     */
    public function show($id = null)
    {
        try {
            $order = $this->orderService->get((int) $id, $this->resolveTenantId());
            return $this->success($order);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * GET /api/v1/orders/:id/timeline
     */
    public function timeline($id = null)
    {
        try {
            $timeline = $this->orderService->getTimeline((int) $id, $this->resolveTenantId());
            return $this->success($timeline);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * POST /api/v1/orders
     */
    public function create()
    {
        // Decode JSON body first; validate() needs data passed explicitly for JSON requests
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'items'              => 'required',
            'items.*.product_id' => 'required|integer|greater_than[0]',
            'items.*.quantity'   => 'required|integer|greater_than[0]',
            'notes'              => 'permit_empty|string|max_length[500]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        // Explicit array check (CI4 validation can be inconsistent with nested arrays)
        if (empty($body['items']) || !is_array($body['items'])) {
            return $this->error('items must be a non-empty array.', 422);
        }

        try {
            $user  = $this->authUser();
            $order = $this->orderService->create(
                $body,
                $this->resolveTenantId(),
                $user['id'],
            );

            return $this->created($order, 'Order placed successfully.');
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * PUT /api/v1/orders/:id/status
     */
    public function updateStatus($id = null)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = ['status' => 'required|in_list[confirmed,preparing,ready,delivered,cancelled]'];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        try {
            $user  = $this->authUser();
            $order = $this->orderService->updateStatus(
                (int) $id,
                $body['status'],
                $this->resolveTenantId(),
                $user['id'],
            );

            return $this->success($order, 'Order status updated.');
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * DELETE /api/v1/orders/:id
     */
    public function cancel($id = null)
    {
        try {
            $user = $this->authUser();
            $this->orderService->cancel((int) $id, $this->resolveTenantId(), $user['id']);
            return $this->success(null, 'Order cancelled.');
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
