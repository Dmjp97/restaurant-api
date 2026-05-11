<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'tenant_id', 'name', 'email', 'password', 'role', 'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'      => 'required|min_length[2]|max_length[150]',
        'email'     => 'required|valid_email|max_length[255]|is_unique[users.email,id,{id}]',
        'password'  => 'required|min_length[6]',
        'role'      => 'required|in_list[superadmin,manager,cashier,kitchen]',
        'tenant_id' => 'required|integer',
    ];

    /** Hash password before insert/update if present in data. */
    protected function initialize(): void
    {
        $this->beforeInsert[] = 'hashPassword';
        $this->beforeUpdate[] = 'hashPassword';
    }

    protected function hashPassword(array $data): array
    {
        if (!empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }

        return $data;
    }
}
