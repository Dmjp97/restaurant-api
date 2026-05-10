<?php

namespace App\Filters;

use App\Libraries\JWTHandler;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * JWTAuthFilter
 *
 * Validates Bearer JWT on every protected route.
 * On success, injects the authenticated user into the request.
 */
class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Missing or malformed Authorization header.');
        }

        $token = substr($authHeader, 7);

        try {
            $jwt     = new JWTHandler();
            $payload = $jwt->decode($token);

            // Check token type — only access tokens allowed here
            if (($payload->type ?? '') !== 'access') {
                return $this->unauthorized('Invalid token type.');
            }

            // Load user and attach to request globals for downstream use
            $userModel = new UserModel();
            $user      = $userModel->find($payload->sub);

            if (!$user || !$user['is_active']) {
                return $this->unauthorized('User not found or inactive.');
            }

            // Store authenticated user in singleton service
            service('auth')->setUser($user);

        } catch (\Exception $e) {
            return $this->unauthorized($e->getMessage());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after
    }

    private function unauthorized(string $message): ResponseInterface
    {
        return response()
            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }
}
