<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'wiki_categories';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'slug', 'name', 'description', 'parent_id',
        'list_order', 'post_level', 'post_users', 'owner',
    ];

    protected $validationRules = [
        'slug' => 'required|max_length[100]|alpha_dash',
        'name' => 'required|max_length[100]',
    ];

    /** 親カテゴリーと合わせてツリー状に返す（各ノードに full_path を付与） */
    public function getTree(): array
    {
        $all = $this->orderBy('list_order')->findAll();
        return $this->buildTree($all);
    }

    private function buildTree(array $items, ?int $parentId = null, string $parentPath = ''): array
    {
        $branch = [];
        foreach ($items as $item) {
            $itemParentId = $item['parent_id'] === null ? null : (int) $item['parent_id'];
            if ($itemParentId === $parentId) {
                $fullPath           = $parentPath !== '' ? $parentPath . '/' . $item['slug'] : $item['slug'];
                $item['full_path']  = $fullPath;
                $item['children']   = $this->buildTree($items, (int) $item['id'], $fullPath);
                $branch[] = $item;
            }
        }
        return $branch;
    }

    /** スラッグの配列でカテゴリーを階層順に辿って返す（例: ['php','wordpress']） */
    public function getBySlugPath(array $slugs): ?array
    {
        $all      = $this->findAll();
        $byParent = [];
        foreach ($all as $cat) {
            $pid = $cat['parent_id'] === null ? 0 : (int) $cat['parent_id'];
            $byParent[$pid][$cat['slug']] = $cat;
        }

        $parentKey = 0;
        $category  = null;
        foreach ($slugs as $slug) {
            $category = $byParent[$parentKey][$slug] ?? null;
            if ($category === null) {
                return null;
            }
            $parentKey = (int) $category['id'];
        }
        return $category;
    }

    /** ツリーを depth 付きフラット配列に展開して返す（select 用） */
    public function getFlatTree(): array
    {
        $flat = [];
        $this->flattenTree($this->getTree(), $flat, 0);
        return $flat;
    }

    private function flattenTree(array $nodes, array &$flat, int $depth): void
    {
        foreach ($nodes as $node) {
            $children    = $node['children'] ?? [];
            unset($node['children']);
            $node['depth'] = $depth;
            $flat[]        = $node;
            if ($children) {
                $this->flattenTree($children, $flat, $depth + 1);
            }
        }
    }

    /** カテゴリーIDとその子孫すべてのIDを返す */
    public function getDescendantIds(int $id): array
    {
        $all = $this->findAll();
        $map = [];
        foreach ($all as $cat) {
            $pid        = $cat['parent_id'] === null ? 0 : (int) $cat['parent_id'];
            $map[$pid][] = (int) $cat['id'];
        }
        $ids   = [$id];
        $queue = [$id];
        while ($queue) {
            $pid = array_shift($queue);
            foreach ($map[$pid] ?? [] as $cid) {
                $ids[]   = $cid;
                $queue[] = $cid;
            }
        }
        return $ids;
    }

    /** カテゴリーIDからルートまでのスラッグパスを返す（例: 'php/wordpress'） */
    public function getFullSlugPath(int $id): string
    {
        $all  = $this->findAll();
        $map  = array_column($all, null, 'id');
        $slugs = [];
        $cur  = $map[$id] ?? null;
        while ($cur !== null) {
            array_unshift($slugs, $cur['slug']);
            $cur = $cur['parent_id'] ? ($map[(int) $cur['parent_id']] ?? null) : null;
        }
        return implode('/', $slugs);
    }

    /** contributor が投稿可能なカテゴリーIDリストを返す */
    public function getAllowedCategoryIds(int $userId, string $username): array
    {
        $rows = $this->where('post_level', 1)->findAll();
        $allowed = [];
        foreach ($rows as $row) {
            $ids = array_filter(array_map('intval', explode(',', (string) $row['post_users'])));
            if (in_array($userId, $ids, true)) {
                $allowed[] = (int) $row['id'];
            }
        }
        return $allowed;
    }
}
