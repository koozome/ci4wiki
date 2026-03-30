<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Libraries\FileUploadService;
use App\Models\ArticleModel;
use App\Models\AttachmentModel;
use App\Models\CategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Articles extends AdminController
{
    public function index(): string
    {
        $articles = model(ArticleModel::class)
            ->select('wiki_articles.*, wiki_categories.name as category_name')
            ->join('wiki_categories', 'wiki_categories.id = wiki_articles.category_id')
            ->orderBy('wiki_articles.updated_at', 'DESC')
            ->findAll();

        return $this->render('admin/articles/index', compact('articles'));
    }

    public function drafts(): string
    {
        $articles = model(ArticleModel::class)
            ->select('wiki_articles.*, wiki_categories.name as category_name')
            ->join('wiki_categories', 'wiki_categories.id = wiki_articles.category_id')
            ->where('wiki_articles.status', 'draft')
            ->orderBy('wiki_articles.updated_at', 'DESC')
            ->findAll();

        return $this->render('admin/articles/drafts', compact('articles'));
    }

    public function add(?int $categoryId = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('articles.create');
        $categories = model(CategoryModel::class)->getFlatTree();

        if ($this->request->getMethod() === 'POST') {
            return $this->save(null);
        }

        return $this->render('admin/articles/add', compact('categories', 'categoryId'));
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $article = model(ArticleModel::class)
            ->select('wiki_articles.*, wiki_categories.slug as category_slug')
            ->join('wiki_categories', 'wiki_categories.id = wiki_articles.category_id')
            ->find($id);
        if ($article === null) {
            throw new PageNotFoundException();
        }

        $this->authorizeEdit($article);

        if ($this->request->getMethod() === 'POST') {
            return $this->save($id);
        }

        $categories  = model(CategoryModel::class)->getFlatTree();
        $attachments = model(AttachmentModel::class)->getByArticle($id);

        return $this->render('admin/articles/edit', compact('article', 'categories', 'attachments'));
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('articles.delete');
        model(ArticleModel::class)->delete($id);
        return redirect()->to('admin/articles')->with('message', '記事を削除しました');
    }

    public function publish(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('articles.publish');
        model(ArticleModel::class)->update($id, [
            'status'       => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('message', '記事を公開しました');
    }

    public function unpublish(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('articles.publish');
        model(ArticleModel::class)->update($id, ['status' => 'draft']);
        return redirect()->back()->with('message', '記事を非公開にしました');
    }

    public function upload(int $articleId): \CodeIgniter\HTTP\RedirectResponse
    {
        $article = model(ArticleModel::class)->find($articleId);
        if ($article === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        $this->authorizeEdit($article);

        $file = $this->request->getFile('attachment');
        if ($file === null || ! $file->isValid()) {
            return redirect()->back()->with('errors', ['attachment' => 'ファイルを選択してください']);
        }

        try {
            $data = (new FileUploadService())->upload($file);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('errors', ['attachment' => $e->getMessage()]);
        }

        model(AttachmentModel::class)->insert(array_merge($data, [
            'article_id' => $articleId,
            'owner'      => auth()->user()->username,
        ]));

        return redirect()->to("admin/articles/edit/{$articleId}")->with('message', 'ファイルをアップロードしました');
    }

    public function preview(): \CodeIgniter\HTTP\Response
    {
        helper(['markdown', 'wiki']);
        $html = markdown_to_html((string) $this->request->getPost('content'));
        return $this->response->setJSON(['html' => $html, 'csrf' => csrf_hash()]);
    }

    public function uploadAjax(int $articleId): \CodeIgniter\HTTP\Response
    {
        $article = model(ArticleModel::class)->find($articleId);
        if ($article === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }
        $this->authorizeEdit($article);

        $file = $this->request->getFile('attachment');
        if ($file === null || ! $file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ファイルを選択してください']);
        }

        try {
            $data = (new FileUploadService())->upload($file);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $e->getMessage()]);
        }

        model(AttachmentModel::class)->insert(array_merge($data, [
            'article_id' => $articleId,
            'owner'      => auth()->user()->username,
        ]));

        $name     = $data['original_name'];
        $isImage  = str_starts_with($data['mime_type'], 'image/');
        $markdown = $isImage ? "![]({$name})" : "[{$name}]({$name})";

        return $this->response->setJSON(compact('markdown'));
    }

    public function deleteAttachment(int $attachmentId): \CodeIgniter\HTTP\RedirectResponse
    {
        $att = model(AttachmentModel::class)->find($attachmentId);
        if ($att === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        (new FileUploadService())->delete($att['filename']);
        model(AttachmentModel::class)->delete($attachmentId);

        return redirect()->to("admin/articles/edit/{$att['article_id']}")->with('message', 'ファイルを削除しました');
    }

    private function save(?int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost(['category_id', 'slug', 'title', 'content', 'status']);

        if ($id === null) {
            $post['owner'] = auth()->user()->username;
        } else {
            $post['editor'] = auth()->user()->username;
        }

        if ($post['status'] === 'published' && $id === null) {
            $post['published_at'] = date('Y-m-d H:i:s');
        }

        $model = model(ArticleModel::class);
        // 同カテゴリー内でのslug重複チェック（編集時は自分自身を除外）
        $dupQuery = $model->where('category_id', $post['category_id'])->where('slug', $post['slug']);
        if ($id) {
            $dupQuery->where('id !=', $id);
        }
        if ($dupQuery->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'このカテゴリーにはすでに同じスラッグの記事が存在します']);
        }
        if (! $model->save(array_merge($post, $id ? ['id' => $id] : []))) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $savedId = $id ?? $model->getInsertID();
        return redirect()->to("admin/articles/edit/{$savedId}")->with('message', '保存しました');
    }

    private function authorizeEdit(array $article): void
    {
        if ($this->hasPermission('articles.edit-all')) {
            return;
        }
        if ($article['owner'] !== auth()->user()->username) {
            header('Location: ' . site_url('admin/articles'));
            exit;
        }
    }
}
