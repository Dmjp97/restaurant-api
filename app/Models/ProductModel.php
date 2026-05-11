<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'tenant_id', 'name', 'sku', 'description', 'category', 'price', 'is_available',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tenant_id' => 'required|integer',
        'name'      => 'required|min_length[2]|max_length[200]',
        'sku'       => 'required|max_length[80]',
        'price'     => 'required|decimal|greater_than[0]',
    ];
}
