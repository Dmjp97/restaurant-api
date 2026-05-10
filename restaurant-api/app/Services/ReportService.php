<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * ReportService
 *
 * Aggregated metrics for managers and superadmins.
 * All queries are read-only and benefit from the CI4 cache layer.
 */
class ReportService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * Daily / weekly / monthly revenue grouped by day.
     */
    public function sales(int $tenantId, string $period, ?string $from = null, ?string $to = null): array
    {
        [$from, $to] = $this->resolveDateRange($period, $from, $to);

        $cacheKey = "report:sales:{$tenantId}:{$period}:{$from}:{$to}";

        return cache()->remember($cacheKey, 300, function () use ($tenantId, $from, $to) {
            $rows = $this->db->query(
                "SELECT
                    DATE(created_at)        AS date,
                    COUNT(*)                AS total_orders,
                    SUM(total)              AS revenue,
                    AVG(total)              AS avg_order_value
                 FROM orders
                 WHERE tenant_id = ?
                   AND status    = 'delivered'
                   AND created_at BETWEEN ? AND ?
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$tenantId, $from . ' 00:00:00', $to . ' 23:59:59']
            )->getResultArray();

            $totalRevenue = array_sum(array_column($rows, 'revenue'));
            $totalOrders  = array_sum(array_column($rows, 'total_orders'));

            return [
                'period'        => compact('from', 'to'),
                'summary'       => [
                    'total_revenue' => round($totalRevenue, 2),
                    'total_orders'  => $totalOrders,
                    'avg_per_day'   => $totalOrders > 0
                        ? round($totalRevenue / max(1, count($rows)), 2)
                        : 0,
                ],
                'daily_breakdown' => $rows,
            ];
        });
    }

    /**
     * Top N products by units sold and revenue in a date range.
     */
    public function topProducts(int $tenantId, string $period, int $limit = 10): array
    {
        [$from, $to] = $this->resolveDateRange($period);

        $cacheKey = "report:top_products:{$tenantId}:{$period}:{$limit}";

        return cache()->remember($cacheKey, 300, function () use ($tenantId, $from, $to, $limit) {
            return $this->db->query(
                "SELECT
                    p.id,
                    p.name,
                    p.sku,
                    p.category,
                    SUM(oi.quantity)  AS units_sold,
                    SUM(oi.subtotal)  AS revenue
                 FROM order_items oi
                 INNER JOIN orders  o ON o.id  = oi.order_id
                 INNER JOIN products p ON p.id = oi.product_id
                 WHERE o.tenant_id  = ?
                   AND o.status     = 'delivered'
                   AND o.created_at BETWEEN ? AND ?
                 GROUP BY p.id, p.name, p.sku, p.category
                 ORDER BY revenue DESC
                 LIMIT ?",
                [$tenantId, $from . ' 00:00:00', $to . ' 23:59:59', $limit]
            )->getResultArray();
        });
    }

    /**
     * Order count breakdown by current status.
     */
    public function ordersByStatus(int $tenantId): array
    {
        return $this->db->query(
            "SELECT status, COUNT(*) AS total
             FROM orders
             WHERE tenant_id = ?
             GROUP BY status",
            [$tenantId]
        )->getResultArray();
    }

    /**
     * Revenue per tenant — superadmin only.
     */
    public function revenueByTenant(string $period): array
    {
        [$from, $to] = $this->resolveDateRange($period);

        return $this->db->query(
            "SELECT
                t.id,
                t.name   AS tenant_name,
                COUNT(o.id)    AS total_orders,
                SUM(o.total)   AS revenue
             FROM tenants t
             LEFT JOIN orders o
                ON o.tenant_id = t.id
               AND o.status    = 'delivered'
               AND o.created_at BETWEEN ? AND ?
             GROUP BY t.id, t.name
             ORDER BY revenue DESC",
            [$from . ' 00:00:00', $to . ' 23:59:59']
        )->getResultArray();
    }

    // ──────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────

    private function resolveDateRange(string $period, ?string $from = null, ?string $to = null): array
    {
        if ($from && $to) {
            return [$from, $to];
        }

        $to   = date('Y-m-d');
        $from = match ($period) {
            'weekly'  => date('Y-m-d', strtotime('-7 days')),
            'monthly' => date('Y-m-d', strtotime('-30 days')),
            'yearly'  => date('Y-m-d', strtotime('-365 days')),
            default   => date('Y-m-d'), // daily
        };

        return [$from, $to];
    }
}
