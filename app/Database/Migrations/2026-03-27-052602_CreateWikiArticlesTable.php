<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWikiArticlesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'category_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 200],
            'title'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'content'      => ['type' => 'LONGTEXT', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['draft', 'published'], 'default' => 'draft'],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'view_count'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'owner'        => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => '作成者username'],
            'editor'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'comment' => '最終編集者username'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['category_id', 'slug']);
        $this->forge->addKey('status');
        $this->forge->addKey('owner');
        $this->forge->addKey('published_at');

        $this->forge->createTable('wiki_articles');

        $this->db->query('ALTER TABLE wiki_articles ADD FULLTEXT INDEX ft_search (title, content)');
        $this->db->query('ALTER TABLE wiki_articles ADD CONSTRAINT fk_article_category FOREIGN KEY (category_id) REFERENCES wiki_categories(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->forge->dropTable('wiki_articles', true);
    }
}
