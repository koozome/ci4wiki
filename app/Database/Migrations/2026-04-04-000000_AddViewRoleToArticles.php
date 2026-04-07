<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddViewRoleToArticles extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('wiki_articles', [
            'view_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('wiki_articles', 'view_role');
    }
}
