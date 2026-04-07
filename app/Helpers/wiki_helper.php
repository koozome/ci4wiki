<?php

declare(strict_types=1);

use App\Models\ArticleModel;
use App\Models\CategoryModel;

if (! function_exists('parse_wiki_links')) {
    /**
     * [[slug]] または [[category/slug]] をHTMLリンクに変換する。
     * 記事が存在しない場合は span.wiki-broken で表示。
     */
    function parse_wiki_links(string $text): string
    {
        return preg_replace_callback(
            '/\[\[([^\]]+)\]\]/',
            static function (array $matches): string {
                $inner = trim($matches[1]);
                $parts = explode('/', $inner, 2);

                if (count($parts) === 2) {
                    [$categorySlug, $articleSlug] = $parts;
                } else {
                    $categorySlug = null;
                    $articleSlug  = $parts[0];
                }

                $article = model(ArticleModel::class)
                    ->select('wiki_articles.id, wiki_articles.title, wiki_articles.slug, wiki_articles.status, wiki_categories.slug as cat_slug')
                    ->join('wiki_categories', 'wiki_categories.id = wiki_articles.category_id');

                if ($categorySlug !== null) {
                    $article->where('wiki_categories.slug', $categorySlug);
                }

                $article = $article->where('wiki_articles.slug', $articleSlug)->first();

                if ($article === null) {
                    if (auth()->loggedIn()) {
                        $u = auth()->user();
                        if ($u->inGroup('editor') || $u->inGroup('moderator') || $u->inGroup('administrator')) {
                            $params = ['slug' => $articleSlug];
                            if ($categorySlug !== null) {
                                $params['category_slug'] = $categorySlug;
                            }
                            $createUrl = site_url('admin/articles/add?' . http_build_query($params));
                            return '<a href="' . $createUrl . '" class="wiki-broken wiki-broken-create" title="記事が存在しません（クリックで作成）">' . esc($inner) . '</a>';
                        }
                    }
                    return '<span class="wiki-broken" title="記事が見つかりません">' . esc($inner) . '</span>';
                }

                $url = site_url($article['cat_slug'] . '/' . $article['slug']);
                $title = esc($article['title']);
                $class = $article['status'] === 'draft' ? ' class="wiki-draft"' : '';

                return "<a href=\"{$url}\"{$class}>{$title}</a>";
            },
            $text
        );
    }
}

if (! function_exists('wiki_breadcrumb')) {
    /**
     * カテゴリーと記事タイトルからパンくずリスト配列を返す。
     * 戻り値: [['label' => '...', 'url' => '...'], ...]
     */
    function wiki_breadcrumb(?array $category = null, ?string $articleTitle = null): array
    {
        $crumbs = [['label' => 'Home', 'url' => site_url('/')]];

        if ($category !== null) {
            $path     = $category['full_path'] ?? model(\App\Models\CategoryModel::class)->getFullSlugPath((int) $category['id']);
            $crumbs[] = ['label' => $category['name'], 'url' => site_url('category/' . $path)];
        }

        if ($articleTitle !== null) {
            $crumbs[] = ['label' => $articleTitle, 'url' => ''];
        }

        return $crumbs;
    }
}

if (! function_exists('article_url')) {
    /** 記事配列からURLを返す（category_slug または category_id が必須） */
    function article_url(array $article): string
    {
        if (isset($article['category_slug'])) {
            return site_url($article['category_slug'] . '/' . $article['slug']);
        }
        $cat = model(CategoryModel::class)->find($article['category_id']);
        return site_url(($cat['slug'] ?? '') . '/' . $article['slug']);
    }
}

if (! function_exists('resolve_attachment_filenames')) {
    /**
     * Markdown 中のファイル名だけの参照を添付ファイルURLに解決する。
     *
     * 対象: ![alt](filename.ext) / [text](filename.ext)
     * 条件: スキーム(://)なし・先頭スラッシュなし・拡張子あり
     */
    function resolve_attachment_filenames(string $content): string
    {
        return preg_replace_callback(
            '/(!?\[[^\]]*\])\(([^)\s\/][^)\s]*\.[a-zA-Z0-9]{1,10})\)/',
            static function (array $m): string {
                $filename = $m[2];
                // スキームや絶対パスは無視
                if (str_contains($filename, '://') || str_starts_with($filename, '/')) {
                    return $m[0];
                }
                $att = \Config\Database::connect()->table('wiki_attachments')
                    ->where('original_name', $filename)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()->getRowArray();
                if ($att === null) {
                    return $m[0];
                }
                $url = site_url('attachment/' . $att['id'] . '/' . rawurlencode($att['original_name']));
                return $m[1] . '(' . $url . ')';
            },
            $content
        );
    }
}

if (! function_exists('slug_from_title')) {
    /**
     * タイトル文字列からURLスラッグを生成する。
     * 英数字・ハイフン以外はハイフンに置換し、小文字化する。
     */
    function slug_from_title(string $title): string
    {
        $slug = mb_strtolower($title);
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
        return trim((string) $slug, '-');
    }
}
