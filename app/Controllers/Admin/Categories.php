<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\CategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Categories extends AdminController
{
    public function __construct()
    {
        // categories.manage 権限が必要
    }

    public function index(): string
    {
        $this->requirePermission('categories.manage');
        $categories = model(CategoryModel::class)->getTree();
        return $this->render('admin/categories/index', compact('categories'));
    }

    public function add(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('categories.manage');
        $parents = model(CategoryModel::class)->orderBy('list_order')->findAll();

        if ($this->request->getMethod() === 'POST') {
            return $this->save(null);
        }

        return $this->render('admin/categories/add', compact('parents'));
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('categories.manage');
        $category = model(CategoryModel::class)->find($id);
        if ($category === null) {
            throw new PageNotFoundException();
        }

        $parents = model(CategoryModel::class)->where('id !=', $id)->orderBy('list_order')->findAll();

        if ($this->request->getMethod() === 'POST') {
            return $this->save($id);
        }

        return $this->render('admin/categories/edit', compact('category', 'parents'));
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('categories.manage');
        model(CategoryModel::class)->delete($id);
        return redirect()->to('admin/categories')->with('message', 'カテゴリーを削除しました');
    }

    public function reorder(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('categories.manage');
        $orders = $this->request->getPost('order') ?? [];
        foreach ($orders as $id => $order) {
            model(CategoryModel::class)->update((int) $id, ['list_order' => (int) $order]);
        }
        return redirect()->to('admin/categories')->with('message', '並び順を更新しました');
    }

    private function save(?int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost(['slug', 'name', 'description', 'parent_id', 'list_order', 'post_level', 'post_users']);
        $post['parent_id']  = $post['parent_id'] ?: null;
        $post['post_level'] = (int) ($post['post_level'] ?? 0);

        if ($id === null) {
            $post['owner'] = auth()->user()->username;
        }

        $model    = model(CategoryModel::class);
        // (parent_id, slug) 複合ユニークを手動チェック（編集時は自分自身を除外）
        $dupQuery = $model->where('slug', $post['slug']);
        if ($post['parent_id'] !== null) {
            $dupQuery->where('parent_id', $post['parent_id']);
        } else {
            $dupQuery->where('parent_id IS NULL', null, false);
        }
        if ($id) {
            $dupQuery->where('id !=', $id);
        }
        if ($dupQuery->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('errors', ['slug' => '同じ親カテゴリー内にすでに同じスラッグが存在します']);
        }

        if (! $model->save(array_merge($post, $id ? ['id' => $id] : []))) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('admin/categories')->with('message', '保存しました');
    }
}
