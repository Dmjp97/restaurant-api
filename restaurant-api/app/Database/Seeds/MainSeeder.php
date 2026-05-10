<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MainSeeder
 *
 * Seeds the database with realistic restaurant data for demo / QA purposes.
 * Run with: php spark db:seed MainSeeder
 */
class MainSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTenants();
        $this->seedUsers();
        $this->seedProducts();
        $this->seedOrders();
    }

    private function seedTenants(): void
    {
        $this->db->table('tenants')->insertBatch([
            ['id' => 1, 'name' => 'BurgerHouse Madrid',    'slug' => 'burgerhouse-madrid',    'plan' => 'pro',        'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'PizzaLab Barcelona',    'slug' => 'pizzalab-barcelona',    'plan' => 'enterprise', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 3, 'name' => 'TacoRun Valencia',      'slug' => 'tacorun-valencia',      'plan' => 'basic',      'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);
    }

    private function seedUsers(): void
    {
        $password = password_hash('password123', PASSWORD_BCRYPT);

        $this->db->table('users')->insertBatch([
            ['tenant_id' => 1, 'name' => 'Super Admin',       'email' => 'superadmin@platform.com', 'password' => $password, 'role' => 'superadmin', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 1, 'name' => 'Ana García',        'email' => 'manager@burgerhouse.com', 'password' => $password, 'role' => 'manager',    'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 1, 'name' => 'Luis Martínez',     'email' => 'cashier@burgerhouse.com', 'password' => $password, 'role' => 'cashier',    'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 2, 'name' => 'Marta Sánchez',     'email' => 'manager@pizzalab.com',    'password' => $password, 'role' => 'manager',    'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 2, 'name' => 'Carlos Ruiz',       'email' => 'kitchen@pizzalab.com',    'password' => $password, 'role' => 'kitchen',    'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);
    }

    private function seedProducts(): void
    {
        $this->db->table('products')->insertBatch([
            // BurgerHouse Madrid (tenant 1)
            ['tenant_id' => 1, 'name' => 'Classic Smash Burger',    'sku' => 'BH-001', 'category' => 'burgers',   'price' => 8.90,  'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 1, 'name' => 'Double Bacon Burger',     'sku' => 'BH-002', 'category' => 'burgers',   'price' => 11.50, 'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 1, 'name' => 'Crispy Chicken Sandwich', 'sku' => 'BH-003', 'category' => 'sandwiches','price' => 9.20,  'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 1, 'name' => 'Sweet Potato Fries',      'sku' => 'BH-004', 'category' => 'sides',     'price' => 3.50,  'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 1, 'name' => 'Craft Lemonade',          'sku' => 'BH-005', 'category' => 'drinks',    'price' => 2.80,  'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            // PizzaLab Barcelona (tenant 2)
            ['tenant_id' => 2, 'name' => 'Margherita DOC',          'sku' => 'PL-001', 'category' => 'pizzas',    'price' => 10.00, 'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 2, 'name' => 'Truffle Prosciutto',      'sku' => 'PL-002', 'category' => 'pizzas',    'price' => 15.90, 'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['tenant_id' => 2, 'name' => 'Burrata Salad',           'sku' => 'PL-003', 'category' => 'salads',    'price' => 8.00,  'is_available' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);
    }

    private function seedOrders(): void
    {
        // Sample completed orders for reports
        for ($i = 0; $i < 20; $i++) {
            $daysAgo = rand(1, 30);

            $this->db->table('orders')->insert([
                'tenant_id'  => 1,
                'user_id'    => 3,
                'status'     => 'delivered',
                'total'      => rand(12, 45) + 0.90,
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")),
                'updated_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")),
            ]);

            $orderId = $this->db->insertID();

            $this->db->table('order_items')->insert([
                'order_id'   => $orderId,
                'product_id' => rand(1, 5),
                'quantity'   => rand(1, 3),
                'unit_price' => 8.90,
                'subtotal'   => 8.90 * rand(1, 3),
            ]);

            $this->db->table('order_timeline')->insertBatch([
                ['order_id' => $orderId, 'status' => 'pending',   'changed_by' => 3, 'created_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"))],
                ['order_id' => $orderId, 'status' => 'confirmed', 'changed_by' => 2, 'created_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days +2 minutes"))],
                ['order_id' => $orderId, 'status' => 'delivered', 'changed_by' => 2, 'created_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days +20 minutes"))],
            ]);
        }
    }
}
