<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ArticleModel;
use App\Models\CategoryModel;

class Dashboard extends AdminController
{
    public function index(): string
    {
        $data = [
            'totalArticles'     => model(ArticleModel::class)->countAll(),
            'publishedArticles' => model(ArticleModel::class)->where('status', 'published')->countAllResults(),
            'draftArticles'     => model(ArticleModel::class)->where('status', 'draft')->countAllResults(),
            'totalCategories'   => model(CategoryModel::class)->countAll(),
            'recentArticles'    => model(ArticleModel::class)
                ->orderBy('updated_at', 'DESC')
                ->limit(10)
                ->findAll(),
        ];

        return $this->render('admin/dashboard', $data);
    }
}
