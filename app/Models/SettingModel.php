<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'wiki_settings';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['setting_key', 'setting_value'];

    /** key => value の連想配列で全設定を返す */
    public function getAll(): array
    {
        $rows = $this->findAll();
        return array_column($rows, 'setting_value', 'setting_key');
    }

    /** 単一キーの値を返す */
    public function getValue(string $key, string $default = ''): string
    {
        $row = $this->where('setting_key', $key)->first();
        return $row ? (string) $row['setting_value'] : $default;
    }

    /** 単一キーを保存（upsert） */
    public function setValue(string $key, string $value): void
    {
        $existing = $this->where('setting_key', $key)->first();
        if ($existing) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->insert(['setting_key' => $key, 'setting_value' => $value]);
        }
    }
}
