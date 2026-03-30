<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AttachmentModel extends Model
{
    protected $table         = 'wiki_attachments';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $updatedField  = '';   // updated_at カラムなし
    protected $allowedFields = [
        'article_id', 'filename', 'original_name', 'mime_type', 'file_size', 'owner',
    ];

    public function getByArticle(int $articleId): array
    {
        return $this->where('article_id', $articleId)->findAll();
    }
}
