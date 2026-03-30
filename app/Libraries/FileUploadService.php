<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class FileUploadService
{
    private int $maxSizeBytes;

    public function __construct()
    {
        $mb = (int) env('UPLOAD_MAX_MB', 64);
        $this->maxSizeBytes = $mb * 1024 * 1024;
    }

    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'application/pdf',
        'text/plain',
        'application/zip',
    ];

    /**
     * アップロードファイルを保存し、DB登録用データを返す。
     *
     * @return array{filename: string, original_name: string, mime_type: string, file_size: int}
     * @throws \RuntimeException バリデーション失敗またはファイル移動失敗
     */
    public function upload(UploadedFile $file): array
    {
        if (! $file->isValid()) {
            throw new \RuntimeException($file->getErrorString());
        }

        if ($file->getSize() > $this->maxSizeBytes) {
            $mb = (int) env('UPLOAD_MAX_MB', 64);
            throw new \RuntimeException("ファイルサイズは{$mb}MB以下にしてください");
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \RuntimeException('許可されていないファイル形式です: ' . $mime);
        }

        $dir      = 'uploads/wiki/' . date('Y') . '/' . date('m') . '/';
        $savePath = FCPATH . $dir;

        if (! is_dir($savePath) && ! mkdir($savePath, 0755, true)) {
            throw new \RuntimeException('アップロードディレクトリの作成に失敗しました');
        }

        $newName = $file->getRandomName();
        $file->move($savePath, $newName);

        return [
            'filename'      => $dir . $newName,
            'original_name' => $file->getClientName(),
            'mime_type'     => $mime,
            'file_size'     => $file->getSize(),
        ];
    }

    /**
     * ファイルをストレージから削除する。
     */
    public function delete(string $filename): void
    {
        $path = FCPATH . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
