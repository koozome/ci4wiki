<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTwofaToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'twofa_type'  => ['type' => 'VARCHAR', 'constraint' => 10,  'null' => true, 'default' => null, 'after' => 'active'],
            'totp_secret' => ['type' => 'VARCHAR', 'constraint' => 64,  'null' => true, 'default' => null, 'after' => 'twofa_type'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['twofa_type', 'totp_secret']);
    }
}
