<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * BaseApiController
 *
 * Standardises all JSON responses across the API.
 * Every V1 controller extends this class.
 */
abstract class BaseApiController extends ResourceController
{
    protected $format = 'json';

    // ── Success responses ──────────────────────

    protected function success(mixed $data, string $message = 'OK', int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ]);
    }

    protected function paginated(array $result, string $message = 'OK'): ResponseInterface
    {
        return $this->response->setStatusCode(200)->setJSON([
            'status'    => 'success',
            'message'   => $message,
            'data'      => $result['data'],
            'meta'      => [
                'total'     => $result['total'],
                'page'      => $result['page'],
                'per_page'  => $result['per_page'],
                'last_page' => $result['last_page'],
            ],
        ]);
    }

    protected function created(mixed $data, string $message = 'Created'): ResponseInterface
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): ResponseInterface
    {
        return $this->response->setStatusCode(204);
    }

    // ── Error responses ────────────────────────

    protected function error(string $message, int $status = 400, ?array $errors = null): ResponseInterface
    {
        $body = ['status' => 'error', 'message' => $message];

        if ($errors) {
            $body['errors'] = $errors;
        }

        return $this->response->setStatusCode($status)->setJSON($body);
    }

    protected function notFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->error($message, 404);
    }

    protected function validationError(array $errors): ResponseInterface
    {
        return $this->error('Validation failed.', 422, $errors);
    }

    // ── Helpers ───────────────────────────────

    /** Return current authenticated user injected by JWTAuthFilter. */
    protected function authUser(): array
    {
        return service('auth')->user();
    }

    /** Resolve tenant_id — superadmin may pass any, others are locked to theirs. */
    protected function resolveTenantId(): int
    {
        $user = $this->authUser();

        if ($user['role'] === 'superadmin') {
            $requested = (int) ($this->request->getGet('tenant_id') ?? $user['tenant_id']);
            return $requested ?: $user['tenant_id'];
        }

        return (int) $user['tenant_id'];
    }
}
