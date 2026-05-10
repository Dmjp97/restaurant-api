<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter
 *
 * Enforces role-based access control (RBAC).
 * Allowed roles are passed as filter arguments from Routes.php.
 *
 * Usage in routes:  ['filter' => 'role:superadmin,manager']
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = service('auth')->user();

        if (!$user) {
            return $this->forbidden('Unauthenticated.');
        }

        // $arguments contains the roles allowed for this route
        if (!empty($arguments) && !in_array($user['role'], $arguments, true)) {
            return $this->forbidden(
                sprintf('Access denied. Required role(s): %s.', implode(', ', $arguments))
            );
        }

        // Tenant isolation — non-superadmins can only access their own tenant data
        if ($user['role'] !== 'superadmin') {
            $tenantId = $request->getGet('tenant_id') ?? $request->getPost('tenant_id');

            if ($tenantId && (int) $tenantId !== (int) $user['tenant_id']) {
                return $this->forbidden('Cross-tenant access is not permitted.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after
    }

    private function forbidden(string $message): ResponseInterface
    {
        return response()
            ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }
}
