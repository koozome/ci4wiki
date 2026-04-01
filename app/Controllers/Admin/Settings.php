<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\SettingModel;

class Settings extends AdminController
{
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('admin.settings');

        if ($this->request->getMethod() === 'POST') {
            $keys  = ['site_name', 'site_description', 'copyright', 'articles_per_page', 'theme', 'admin_theme'];
            $model = model(SettingModel::class);
            foreach ($keys as $key) {
                $model->setValue($key, (string) $this->request->getPost($key));
            }
            return redirect()->to('admin/settings')->with('message', '設定を保存しました');
        }

        $settings = model(SettingModel::class)->getAll();
        return $this->render('admin/settings/index', compact('settings'));
    }
}
