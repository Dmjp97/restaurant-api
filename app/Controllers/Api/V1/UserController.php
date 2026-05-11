<?php

namespace App\Controllers\Api\V1;

use App\Models\UserModel;

class UserController extends BaseApiController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /** GET /api/v1/users */
    public function index()
    {
        $tenantId = $this->resolveTenantId();
        $page     = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit    = min(100, max(1, (int) ($this->request->getGet('limit') ?? 20)));
        $offset   = ($page - 1) * $limit;

        $builder = $this->userModel
            ->select('id, tenant_id, name, email, role, is_active, created_at, updated_at')
            ->orderBy('name', 'ASC');

        if ($this->authUser()['role'] !== 'superadmin') {
            $builder->where('tenant_id', $tenantId);
        } elseif ($this->request->getGet('tenant_id')) {
            $builder->where('tenant_id', $tenantId);
        }

        $total = $builder->countAllResults(false);
        $users = $builder->limit($limit, $offset)->findAll();

        return $this->paginated([
            'data'      => $users,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $limit,
            'last_page' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /** GET /api/v1/users/:id */
    public function show($id = null)
    {
        $user = $this->findVisibleUser((int) $id);

        return $user ? $this->success($user) : $this->notFound('User not found.');
    }

    /** POST /api/v1/users */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];
        $actor = $this->authUser();

        $rules = [
            'name'      => 'required|min_length[2]|max_length[150]',
            'email'     => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'password'  => 'required|min_length[6]',
            'role'      => 'required|in_list[superadmin,manager,cashier,kitchen]',
            'tenant_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        if ($actor['role'] !== 'superadmin' && $body['role'] === 'superadmin') {
            return $this->error('Only superadmins can create superadmin users.', 403);
        }

        $tenantId = $actor['role'] === 'superadmin'
            ? (int) ($body['tenant_id'] ?? $actor['tenant_id'])
            : (int) $actor['tenant_id'];

        $insertId = $this->userModel->insert([
            'tenant_id'  => $tenantId,
            'name'       => $body['name'],
            'email'      => $body['email'],
            'password'   => $body['password'],
            'role'       => $body['role'],
            'is_active'  => $body['is_active'] ?? 1,
        ], true);

        if ($insertId === false) {
            return $this->error('Failed to create user.', 500, $this->userModel->errors());
        }

        return $this->created($this->findVisibleUser((int) $insertId), 'User created.');
    }

    /** PUT /api/v1/users/:id */
    public function update($id = null)
    {
        $id = (int) $id;
        $user = $this->findVisibleUser($id);

        if (!$user) {
            return $this->notFound('User not found.');
        }

        $body = $this->request->getJSON(true) ?? [];
        $actor = $this->authUser();

        $rules = [
            'name'      => 'permit_empty|min_length[2]|max_length[150]',
            'email'     => "permit_empty|valid_email|max_length[255]|is_unique[users.email,id,{$id}]",
            'password'  => 'permit_empty|min_length[6]',
            'role'      => 'permit_empty|in_list[superadmin,manager,cashier,kitchen]',
            'tenant_id' => 'permit_empty|integer',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        if ($actor['role'] !== 'superadmin') {
            unset($body['tenant_id']);

            if (($body['role'] ?? null) === 'superadmin') {
                return $this->error('Only superadmins can assign the superadmin role.', 403);
            }
        }

        $updateData = array_filter($body, fn ($value) => $value !== null && $value !== '');
        unset($updateData['id']);

        if ($updateData !== []) {
            $this->userModel->update($id, $updateData);
        }

        return $this->success($this->findVisibleUser($id), 'User updated.');
    }

    /** DELETE /api/v1/users/:id */
    public function delete($id = null)
    {
        $id = (int) $id;

        if (!$this->findVisibleUser($id)) {
            return $this->notFound('User not found.');
        }

        if ((int) $this->authUser()['id'] === $id) {
            return $this->error('You cannot delete your own user.', 422);
        }

        $this->userModel->delete($id);

        return $this->success(null, 'User deleted.');
    }

    private function findVisibleUser(int $id): ?array
    {
        $user = $this->userModel
            ->select('id, tenant_id, name, email, role, is_active, created_at, updated_at')
            ->find($id);

        if (!$user) {
            return null;
        }

        $actor = $this->authUser();

        if ($actor['role'] !== 'superadmin' && (int) $user['tenant_id'] !== (int) $actor['tenant_id']) {
            return null;
        }

        return $user;
    }
}
