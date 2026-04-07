<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table         = 'wiki_articles';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'category_id', 'slug', 'title', 'content', 'status', 'view_role',
        'published_at', 'view_count', 'owner', 'editor',
    ];

    /** ロール階層（低→高） */
    public const ROLE_LEVELS = [
        'contributor'   => 1,
        'editor'        => 2,
        'moderator'     => 3,
        'administrator' => 4,
    ];

    /**
     * 閲覧可能な記事のみに絞り込む。
     * $userRole: ログイン中ユーザーの最上位ロール（未ログインは null）
     */
    public function visibleTo(?string $userRole): static
    {
        if ($userRole !== null && isset(self::ROLE_LEVELS[$userRole])) {
            $level   = self::ROLE_LEVELS[$userRole];
            $allowed = array_keys(array_filter(self::ROLE_LEVELS, fn ($l) => $l <= $level));
            return $this->groupStart()
                ->where('wiki_articles.view_role IS NULL', null, false)
                ->orWhereIn('wiki_articles.view_role', $allowed)
                ->groupEnd();
        }
        // 未ログイン or 未知のロール → 公開のみ
        return $this->where('wiki_articles.view_role IS NULL', null, false);
    }

    protected $validationRules = [
        'category_id' => 'required|is_natural_no_zero',
        'slug'        => 'required|max_length[200]|alpha_dash',
        'title'       => 'required|max_length[255]',
        'status'      => 'required|in_list[draft,published]',
    ];

    /** 公開記事をカテゴリーslugで取得 */
    public function getPublishedByCategory(int $categoryId): object
    {
        return $this->select('wiki_articles.*')
            ->where('wiki_articles.category_id', $categoryId)
            ->where('wiki_articles.status', 'published')
            ->orderBy('wiki_articles.published_at', 'DESC');
    }

    /** カテゴリーslug + 記事slug で1件取得 */
    public function getBySlug(string $categorySlug, string $articleSlug): ?array
    {
        return $this->select('wiki_articles.*')
            ->join('wiki_categories', 'wiki_categories.id = wiki_articles.category_id')
            ->where('wiki_categories.slug', $categorySlug)
            ->where('wiki_articles.slug', $articleSlug)
            ->first();
    }

    /** 閲覧数インクリメント */
    public function incrementViewCount(int $id): void
    {
        $this->set('view_count', 'view_count + 1', false)->where('id', $id)->update();
    }

    /** FULLTEXT検索 */
    public function search(string $keyword): array
    {
        $escaped = $this->db->escapeString($keyword);
        return $this->select('wiki_articles.*, wiki_categories.slug as category_slug')
            ->join('wiki_categories', 'wiki_categories.id = wiki_articles.category_id')
            ->where('wiki_articles.status', 'published')
            ->where("MATCH(wiki_articles.title, wiki_articles.content) AGAINST('{$escaped}' IN BOOLEAN MODE)")
            ->findAll();
    }
}
