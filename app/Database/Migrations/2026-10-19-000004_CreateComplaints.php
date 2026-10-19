<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComplaints extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ticket_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'category_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'agency_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'incident_date' => [
                'type' => 'DATE',
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
            ],
            'address_detail' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'village' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'district' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'hide_identity' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'menunggu',
            ],
            'rejected_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rating' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'rating_comment' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('ticket_code');
        $this->forge->addKey('status');
        $this->forge->addKey('agency_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['latitude', 'longitude']);
        $this->forge->addKey(['status', 'agency_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('agency_id', 'agencies', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('complaints', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('complaints', true);
    }
}
