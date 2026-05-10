<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\UnauthorizedException;
use App\Services\AuthService;

class AuthController extends BaseApiController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login()
    {
        // CI4 validate() only reads $_POST by default.
        // For JSON APIs we must pass the decoded body explicitly.
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        try {
            $tokens = $this->authService->login(
                $body['email'],
                $body['password'],
            );

            return $this->success($tokens, 'Login successful.');
        } catch (UnauthorizedException $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    /**
     * POST /api/v1/auth/refresh
     */
    public function refresh()
    {
        $body         = $this->request->getJSON(true) ?? [];
        $refreshToken = $body['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            return $this->error('refresh_token is required.', 422);
        }

        try {
            $tokens = $this->authService->refresh($refreshToken);
            return $this->success($tokens, 'Token refreshed.');
        } catch (UnauthorizedException $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    /**
     * POST /api/v1/auth/logout   [JWT required]
     */
    public function logout()
    {
        $body         = $this->request->getJSON(true) ?? [];
        $refreshToken = $body['refresh_token'] ?? '';

        $this->authService->logout($refreshToken);

        return $this->success(null, 'Logged out successfully.');
    }

    /**
     * GET /api/v1/auth/me   [JWT required]
     */
    public function me()
    {
        $user = $this->authUser();

        return $this->success([
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'tenant_id' => $user['tenant_id'],
        ]);
    }
}
