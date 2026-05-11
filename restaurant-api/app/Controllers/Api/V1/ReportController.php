<?php

namespace App\Controllers\Api\V1;

use App\Services\ReportService;

class ReportController extends BaseApiController
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    /** GET /api/v1/reports/sales?period=monthly&from=2024-01-01&to=2024-01-31 */
    public function sales()
    {
        $tenantId = $this->resolveTenantId();
        $period   = $this->request->getGet('period') ?? 'monthly';
        $from     = $this->request->getGet('from');
        $to       = $this->request->getGet('to');

        $data = $this->reportService->sales($tenantId, $period, $from, $to);

        return $this->success($data);
    }

    /** GET /api/v1/reports/top-products?period=monthly&limit=10 */
    public function topProducts()
    {
        $tenantId = $this->resolveTenantId();
        $period   = $this->request->getGet('period') ?? 'monthly';
        $limit    = min(50, max(1, (int) ($this->request->getGet('limit') ?? 10)));

        return $this->success($this->reportService->topProducts($tenantId, $period, $limit));
    }

    /** GET /api/v1/reports/orders-by-status */
    public function ordersByStatus()
    {
        return $this->success(
            $this->reportService->ordersByStatus($this->resolveTenantId())
        );
    }

    /** GET /api/v1/reports/revenue-by-tenant  (superadmin only) */
    public function revenueByTenant()
    {
        if ($this->authUser()['role'] !== 'superadmin') {
            return $this->error('Access denied. Superadmin role required.', 403);
        }

        $period = $this->request->getGet('period') ?? 'monthly';
        return $this->success($this->reportService->revenueByTenant($period));
    }
}
