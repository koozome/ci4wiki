<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\AttachmentModel;
use App\Models\CategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Wiki extends BaseController
{
    public function index(): string
    {
        $categories = model(CategoryModel::class)->getTree();
        $latest     = model(ArticleModel::class)
            ->visibleTo($this->userRole())
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->limit(10)
            ->findAll();

        return $this->render('wiki/index', compact('categories', 'latest'));
    }

    public function category(string $path): string
    {
        $slugs    = explode('/', trim($path, '/'));
        $catModel = model(CategoryModel::class);
        $category = $catModel->getBySlugPath($slugs);
        if ($category === null) {
            throw new PageNotFoundException();
        }
        $category['full_path'] = $path;

        $categoryIds = $catModel->getDescendantIds((int) $category['id']);

        // 全カテゴリーを list_order 順で取得しマップ構築
        $allCats  = $catModel->orderBy('list_order')->findAll();
        $catMap   = array_column($allCats, null, 'id');
        $byParent = [];
        foreach ($allCats as $c) {
            $pid = $c['parent_id'] === null ? 0 : (int) $c['parent_id'];
            $byParent[$pid][] = $c;
        }

        // 記事を取得して category_id でグループ化（category_slug を事前付与）
        $allArticles = model(ArticleModel::class)
            ->visibleTo($this->userRole())
            ->whereIn('category_id', $categoryIds)
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->findAll();
        $byCategory = [];
        foreach ($allArticles as &$art) {
            $art['category_slug'] = $catMap[$art['category_id']]['slug'] ?? '';
            $byCategory[(int) $art['category_id']][] = $art;
        }
        unset($art);

        // BFS でセクションリスト構築（depth 付き）
        $sections = [];
        $queue    = [[(int) $category['id'], 0]];
        while ($queue) {
            [$cid, $depth] = array_shift($queue);
            $cat = $catMap[$cid] ?? null;
            if ($cat !== null) {
                $sections[] = [
                    'category' => $cat,
                    'articles' => $byCategory[$cid] ?? [],
                    'depth'    => $depth,
                ];
            }
            foreach ($byParent[$cid] ?? [] as $child) {
                $queue[] = [(int) $child['id'], $depth + 1];
            }
        }

        return $this->render('wiki/category', compact('category', 'sections'));
    }

    public function article(string $categorySlug, string $articleSlug): string
    {
        $article = model(ArticleModel::class)->getBySlug($categorySlug, $articleSlug);
        if ($article === null) {
            throw new PageNotFoundException();
        }

        if ($article['status'] === 'draft') {
            if (! auth()->loggedIn()) {
                throw new PageNotFoundException();
            }
            if (
                ! auth()->user()->can('articles.edit-all')
                && $article['owner'] !== auth()->user()->username
            ) {
                throw new PageNotFoundException();
            }
        }

        // 閲覧ロール制限チェック
        if (! $this->canView($article['view_role'] ?? null)) {
            if (! auth()->loggedIn()) {
                return redirect()->to('login');
            }
            throw new PageNotFoundException();
        }

        model(ArticleModel::class)->incrementViewCount((int) $article['id']);
        $attachments = model(AttachmentModel::class)->getByArticle((int) $article['id']);
        $category    = model(CategoryModel::class)->find($article['category_id']);

        return $this->render('wiki/article', compact('article', 'category', 'attachments'));
    }

    public function search(): string
    {
        $keyword  = trim((string) $this->request->getGet('q'));
        $articles = $keyword !== '' ? model(ArticleModel::class)->visibleTo($this->userRole())->search($keyword) : [];

        return $this->render('wiki/search', compact('keyword', 'articles'));
    }

    /** ログイン中ユーザーの最上位ロールを返す（未ログインは null） */
    private function userRole(): ?string
    {
        if (! auth()->loggedIn()) {
            return null;
        }
        $user = auth()->user();
        foreach (['administrator', 'moderator', 'editor', 'contributor'] as $role) {
            if ($user->inGroup($role)) {
                return $role;
            }
        }
        return null;
    }

    /** view_role 設定に対して現在のユーザーが閲覧可能か判定 */
    private function canView(?string $viewRole): bool
    {
        if ($viewRole === null) {
            return true;
        }
        $role = $this->userRole();
        return $role !== null
            && ArticleModel::ROLE_LEVELS[$role] >= ArticleModel::ROLE_LEVELS[$viewRole];
    }

    public function attachment(int $id, ?string $filename = null)
    {
        $attachment = model(AttachmentModel::class)->find($id);
        if ($attachment === null) {
            throw new PageNotFoundException();
        }

        $path = FCPATH . $attachment['filename'];
        if (! is_file($path)) {
            throw new PageNotFoundException();
        }

        $mime   = $attachment['mime_type'] ?? 'application/octet-stream';
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';
        $disposition = ($inline ? 'inline' : 'attachment') . '; filename="' . rawurlencode($attachment['original_name']) . '"';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', $disposition)
            ->setBody(file_get_contents($path));
    }
}
