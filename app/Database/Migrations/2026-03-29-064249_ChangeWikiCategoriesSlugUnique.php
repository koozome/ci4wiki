<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeWikiCategoriesSlugUnique extends Migration
{
    public function up(): void
    {
        // グローバルunique → (parent_id, slug) composite unique に変更
        $this->db->query('ALTER TABLE wiki_categories DROP INDEX slug');
        $this->db->query('ALTER TABLE wiki_categories ADD UNIQUE INDEX cat_parent_slug (parent_id, slug)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE wiki_categories DROP INDEX cat_parent_slug');
        $this->db->query('ALTER TABLE wiki_categories ADD UNIQUE INDEX slug (slug)');
    }
}
