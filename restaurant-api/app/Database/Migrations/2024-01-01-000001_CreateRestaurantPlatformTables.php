<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * CreateRestaurantPlatformTables
 *
 * Single migration that builds the full schema in dependency order:
 * tenants → users → products → orders → order_items → order_timeline
 */
class CreateRestaurantPlatformTables extends Migration
{
    public function up(): void
    {
        // ── Tenants (multi-tenant root) ───────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'plan'       => ['type' => 'ENUM', 'constraint' => ['basic', 'pro', 'enterprise'], 'default' => 'basic'],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('tenants');

        // ── Users ─────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'       => ['type' => 'ENUM', 'constraint' => ['superadmin', 'manager', 'cashier', 'kitchen'], 'default' => 'cashier'],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('users');

        // ── Products ──────────────────────────────────
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 200],
            'sku'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'category'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'price'        => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'is_available' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['tenant_id', 'sku']);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('products');

        // ── Orders ────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['pending','confirmed','preparing','ready','delivered','cancelled'], 'default' => 'pending'],
            'total'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'notes'      => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('orders');

        // ── Order Items ───────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'order_id'   => ['type' => 'INT', 'unsigned' => true],
            'product_id' => ['type' => 'INT', 'unsigned' => true],
            'quantity'   => ['type' => 'INT', 'unsigned' => true],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'subtotal'   => ['type' => 'DECIMAL', 'constraint' => '10,2'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('order_id');
        $this->forge->createTable('order_items');

        // ── Order Timeline ────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'order_id'   => ['type' => 'INT', 'unsigned' => true],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'changed_by' => ['type' => 'INT', 'unsigned' => true],
            'note'       => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('order_id');
        $this->forge->createTable('order_timeline');
    }

    public function down(): void
    {
        foreach (['order_timeline', 'order_items', 'orders', 'products', 'users', 'tenants'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
