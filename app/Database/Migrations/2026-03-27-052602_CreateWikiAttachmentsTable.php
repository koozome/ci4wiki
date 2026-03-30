<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWikiAttachmentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'article_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'filename'      => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => '保存ファイル名(UUID等)'],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => '元ファイル名'],
            'mime_type'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'file_size'     => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'owner'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('article_id');

        $this->forge->createTable('wiki_attachments');

        $this->db->query('ALTER TABLE wiki_attachments ADD CONSTRAINT fk_attachment_article FOREIGN KEY (article_id) REFERENCES wiki_articles(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->forge->dropTable('wiki_attachments', true);
    }
}
