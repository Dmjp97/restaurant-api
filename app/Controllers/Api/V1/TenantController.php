<?php

namespace App\Controllers\Api\V1;

use App\Models\TenantModel;

class TenantController extends BaseApiController
{
    private TenantModel $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
    }

    /** GET /api/v1/tenants */
    public function index()
    {
        $page   = max(1, (int) ($this->request->getGet('page')  ?? 1));
        $limit  = min(100, max(1, (int) ($this->request->getGet('limit') ?? 20)));
        $offset = ($page - 1) * $limit;

        $total   = $this->tenantModel->countAllResults(false);
        $tenants = $this->tenantModel->orderBy('name', 'ASC')->limit($limit, $offset)->findAll();

        return $this->paginated([
            'data'      => $tenants,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $limit,
            'last_page' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /** GET /api/v1/tenants/:id */
    public function show($id = null)
    {
        $tenant = $this->tenantModel->find((int) $id);

        return $tenant
            ? $this->success($tenant)
            : $this->notFound('Tenant not found.');
    }

    /** POST /api/v1/tenants */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'name' => 'required|min_length[2]|max_length[150]',
            'slug' => 'required|max_length[150]|is_unique[tenants.slug]',
            'plan' => 'required|in_list[basic,pro,enterprise]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        // CI4 Model::insert() returns insert ID on success, false on failure
        $insertId = $this->tenantModel->insert($body, true);

        if ($insertId === false) {
            return $this->error('Failed to create tenant.', 500);
        }

        return $this->created($this->tenantModel->find($insertId), 'Tenant created.');
    }

    /** PUT /api/v1/tenants/:id */
    public function update($id = null)
    {
        $tenant = $this->tenantModel->find((int) $id);

        if (!$tenant) {
            return $this->notFound('Tenant not found.');
        }

        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'name' => 'permit_empty|min_length[2]|max_length[150]',
            'slug' => "permit_empty|max_length[150]|is_unique[tenants.slug,id,{$id}]",
            'plan' => 'permit_empty|in_list[basic,pro,enterprise]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        $updateData = array_filter($body, fn($v) => $v !== null);
        unset($updateData['id']);

        if (!empty($updateData)) {
            $this->tenantModel->update($id, $updateData);
        }

        return $this->success($this->tenantModel->find((int) $id), 'Tenant updated.');
    }

    /** DELETE /api/v1/tenants/:id */
    public function delete($id = null)
    {
        $tenant = $this->tenantModel->find((int) $id);

        if (!$tenant) {
            return $this->notFound('Tenant not found.');
        }

        $this->tenantModel->delete((int) $id);

        return $this->success(null, 'Tenant deleted.');
    }
}
