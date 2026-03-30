<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWikiCategoriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'parent_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'list_order'  => ['type' => 'INT', 'default' => 0],
            'post_level'  => ['type' => 'TINYINT', 'default' => 0, 'comment' => '0:editor以上, 1:contributor許可'],
            'post_users'  => ['type' => 'TEXT', 'null' => true, 'comment' => '許可contributorID(カンマ区切り)'],
            'owner'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('parent_id');

        $this->forge->createTable('wiki_categories');

        // 自己参照FK（テーブル作成後に追加）
        $this->db->query('ALTER TABLE wiki_categories ADD CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES wiki_categories(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        $this->forge->dropTable('wiki_categories', true);
    }
}
