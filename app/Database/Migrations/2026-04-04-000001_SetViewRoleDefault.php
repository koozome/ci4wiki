<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetViewRoleDefault extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('wiki_articles', [
            'view_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'contributor',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('wiki_articles', [
            'view_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }
}
