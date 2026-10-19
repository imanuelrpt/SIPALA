<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginAttempts extends Migration
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
            'identifier' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
            ],
            'attempted_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['identifier', 'attempted_at']);
        $this->forge->addKey(['ip_address', 'attempted_at']);
        $this->forge->createTable('login_attempts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('login_attempts', true);
    }
}
