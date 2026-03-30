<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWikiSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'setting_key'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'setting_value' => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('setting_key');

        $this->forge->createTable('wiki_settings');

        $this->db->table('wiki_settings')->insertBatch([
            ['setting_key' => 'site_name',        'setting_value' => 'My Wiki',      'created_at' => date('Y-m-d H:i:s')],
            ['setting_key' => 'site_description', 'setting_value' => 'Wiki System',  'created_at' => date('Y-m-d H:i:s')],
            ['setting_key' => 'copyright',        'setting_value' => '',             'created_at' => date('Y-m-d H:i:s')],
            ['setting_key' => 'articles_per_page','setting_value' => '20',           'created_at' => date('Y-m-d H:i:s')],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('wiki_settings', true);
    }
}
