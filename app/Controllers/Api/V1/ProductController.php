<?php

namespace App\Controllers\Api\V1;

use App\Repositories\ProductRepository;

class ProductController extends BaseApiController
{
    private ProductRepository $productRepo;

    public function __construct()
    {
        $this->productRepo = new ProductRepository();
    }

    /** GET /api/v1/products */
    public function index()
    {
        $tenantId = $this->resolveTenantId();

        // array_filter with !== null is safe: keeps 0, false, '' but drops null
        $filters = array_filter([
            'category'     => $this->request->getGet('category'),
            'search'       => $this->request->getGet('search'),
            'is_available' => $this->request->getGet('is_available'),
        ], fn($v) => $v !== null && $v !== '');

        $page  = max(1, (int) ($this->request->getGet('page')  ?? 1));
        $limit = min(100, max(1, (int) ($this->request->getGet('limit') ?? 20)));

        return $this->paginated($this->productRepo->findByTenant($tenantId, $filters, $page, $limit));
    }

    /** GET /api/v1/products/:id */
    public function show($id = null)
    {
        $product = $this->productRepo->findById((int) $id);

        if (!$product) {
            return $this->notFound('Product not found.');
        }

        $user = $this->authUser();
        if ($user['role'] !== 'superadmin' && (int) $product['tenant_id'] !== (int) $user['tenant_id']) {
            return $this->notFound('Product not found.'); // don't leak cross-tenant existence
        }

        return $this->success($product);
    }

    /** POST /api/v1/products */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'name'         => 'required|min_length[2]|max_length[200]',
            'sku'          => 'required|max_length[80]',
            'price'        => 'required|decimal|greater_than[0]',
            'category'     => 'permit_empty|max_length[100]',
            'description'  => 'permit_empty|max_length[1000]',
            'is_available' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        $id = $this->productRepo->create([
            'tenant_id'    => $this->resolveTenantId(),
            'name'         => $body['name'],
            'sku'          => $body['sku'],
            'price'        => $body['price'],
            'category'     => $body['category']    ?? null,
            'description'  => $body['description'] ?? null,
            'is_available' => $body['is_available'] ?? 1,
        ]);

        return $this->created($this->productRepo->findById($id), 'Product created.');
    }

    /** PUT /api/v1/products/:id */
    public function update($id = null)
    {
        $product = $this->productRepo->findById((int) $id);

        if (!$product) {
            return $this->notFound('Product not found.');
        }

        $user = $this->authUser();
        if ($user['role'] !== 'superadmin' && (int) $product['tenant_id'] !== (int) $user['tenant_id']) {
            return $this->notFound('Product not found.');
        }

        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'name'         => 'permit_empty|min_length[2]|max_length[200]',
            'sku'          => 'permit_empty|max_length[80]',
            'price'        => 'permit_empty|decimal|greater_than[0]',
            'category'     => 'permit_empty|max_length[100]',
            'description'  => 'permit_empty|max_length[1000]',
            'is_available' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules, $body)) {
            return $this->validationError($this->validator->getErrors());
        }

        // Use !== null so is_available=0 and price=0.00 are NOT dropped
        $updateData = array_filter($body, fn($v) => $v !== null);
        unset($updateData['tenant_id'], $updateData['id']); // immutable fields

        if (!empty($updateData)) {
            $this->productRepo->update((int) $id, $updateData);
        }

        return $this->success($this->productRepo->findById((int) $id), 'Product updated.');
    }

    /** DELETE /api/v1/products/:id */
    public function delete($id = null)
    {
        $product = $this->productRepo->findById((int) $id);

        if (!$product) {
            return $this->notFound('Product not found.');
        }

        $user = $this->authUser();
        if ($user['role'] !== 'superadmin' && (int) $product['tenant_id'] !== (int) $user['tenant_id']) {
            return $this->notFound('Product not found.');
        }

        $this->productRepo->delete((int) $id);

        return $this->success(null, 'Product deleted.');
    }
}
