<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'contributor';

    /** @var array<string, array<string, string>> */
    public array $groups = [
        'administrator' => [
            'title'       => '管理者',
            'description' => '全権限。ユーザー管理、カテゴリー管理、サイト設定',
        ],
        'moderator' => [
            'title'       => 'モデレーター',
            'description' => 'カテゴリー管理、全記事管理、contributor登録',
        ],
        'editor' => [
            'title'       => '編集者',
            'description' => '全カテゴリーへの投稿、全記事の編集・公開',
        ],
        'contributor' => [
            'title'       => '投稿者',
            'description' => '許可カテゴリーへの投稿、自記事の編集',
        ],
    ];

    /** @var array<string, string> */
    public array $permissions = [
        'admin.access'             => '管理画面アクセス',
        'admin.settings'           => 'サイト設定',
        'users.manage'             => '全ユーザー管理',
        'users.create-contributor' => 'contributor作成',
        'categories.manage'        => 'カテゴリー管理',
        'articles.create'          => '記事投稿',
        'articles.edit-all'        => '全記事編集',
        'articles.delete'          => '記事削除',
        'articles.publish'         => '記事公開/非公開',
    ];

    /** @var array<string, list<string>> */
    public array $matrix = [
        'administrator' => [
            'admin.*',
            'users.*',
            'categories.*',
            'articles.*',
        ],
        'moderator' => [
            'admin.access',
            'users.create-contributor',
            'categories.manage',
            'articles.create',
            'articles.edit-all',
            'articles.delete',
            'articles.publish',
        ],
        'editor' => [
            'admin.access',
            'articles.create',
            'articles.edit-all',
            'articles.publish',
        ],
        'contributor' => [
            'admin.access',
            'articles.create',
        ],
    ];
}
