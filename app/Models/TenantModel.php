<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table         = 'tenants';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'slug', 'plan', 'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[150]',
        'slug' => 'required|max_length[150]|is_unique[tenants.slug,id,{id}]',
        'plan' => 'required|in_list[basic,pro,enterprise]',
    ];
}
