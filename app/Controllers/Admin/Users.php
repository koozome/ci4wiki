<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Exceptions\PageNotFoundException;

class Users extends AdminController
{
    public function index(): string
    {
        $this->requirePermission('users.manage');
        $users = auth()->getProvider()->findAll();
        return $this->render('admin/users/index', compact('users'));
    }

    public function add(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        // administrator のみ全ユーザー追加可、moderator は contributor のみ
        if (! $this->hasPermission('users.manage') && ! $this->hasPermission('users.create-contributor')) {
            header('Location: ' . site_url('admin'));
            exit;
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->saveNewUser();
        }

        $canAssignRoles = $this->hasPermission('users.manage')
            ? ['administrator', 'moderator', 'editor', 'contributor']
            : ['contributor'];

        return $this->render('admin/users/add', compact('canAssignRoles'));
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('users.manage');
        $user = auth()->getProvider()->findById($id);
        if ($user === null) {
            throw new PageNotFoundException();
        }

        if ($this->request->getMethod() === 'POST') {
            return $this->updateUser($id);
        }

        $roles = ['administrator', 'moderator', 'editor', 'contributor'];
        return $this->render('admin/users/edit', compact('user', 'roles'));
    }

    public function password(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('users.manage');
        $user = auth()->getProvider()->findById($id);
        if ($user === null) {
            throw new PageNotFoundException();
        }

        if ($this->request->getMethod() === 'POST') {
            $user->fill(['password' => $this->request->getPost('password')]);
            auth()->getProvider()->save($user);
            return redirect()->to("admin/users/edit/{$id}")->with('message', 'パスワードを変更しました');
        }

        return $this->render('admin/users/password', compact('user'));
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('users.manage');
        auth()->getProvider()->delete($id, true);
        return redirect()->to('admin/users')->with('message', 'ユーザーを削除しました');
    }

    private function saveNewUser(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post  = $this->request->getPost(['username', 'email', 'password', 'role']);
        $users = auth()->getProvider();

        $user = new User([
            'username' => $post['username'],
            'email'    => $post['email'],
            'password' => $post['password'],
            'active'   => 1,
        ]);

        if (! $users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        $users->findById($users->getInsertID())->addGroup($post['role'] ?? 'contributor');
        return redirect()->to('admin/users')->with('message', 'ユーザーを作成しました');
    }

    private function updateUser(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $post  = $this->request->getPost(['username', 'email', 'role']);
        $users = auth()->getProvider();
        $user  = $users->findById($id);

        $user->fill(['username' => $post['username'], 'email' => $post['email']]);
        $users->save($user);

        // ロール更新
        foreach (['administrator', 'moderator', 'editor', 'contributor'] as $group) {
            $user->removeGroup($group);
        }
        $user->addGroup($post['role']);

        return redirect()->to('admin/users')->with('message', '更新しました');
    }
}
