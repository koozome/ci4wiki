<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 公開ルート（固定パス）
$routes->get('/', 'Wiki::index');
$routes->get('search', 'Wiki::search');
$routes->get('attachment/(:num)', 'Wiki::attachment/$1');
$routes->get('attachment/(:num)/(:any)', 'Wiki::attachment/$1/$2');

// Shield 認証ルート
service('auth')->routes($routes);

// 管理ルート（要ログイン）
$routes->group('admin', ['filter' => 'session'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Dashboard::index');

    // 記事管理
    $routes->get('articles', 'Admin\Articles::index');
    $routes->get('articles/drafts', 'Admin\Articles::drafts');
    $routes->match(['GET', 'POST'], 'articles/add', 'Admin\Articles::add');
    $routes->match(['GET', 'POST'], 'articles/add/(:num)', 'Admin\Articles::add/$1');
    $routes->match(['GET', 'POST'], 'articles/edit/(:num)', 'Admin\Articles::edit/$1');
    $routes->post('articles/delete/(:num)', 'Admin\Articles::delete/$1');
    $routes->post('articles/upload/(:num)', 'Admin\Articles::upload/$1');
    $routes->post('articles/upload-ajax/(:num)', 'Admin\Articles::uploadAjax/$1');
    $routes->post('articles/preview', 'Admin\Articles::preview');
    $routes->post('articles/attachment-delete/(:num)', 'Admin\Articles::deleteAttachment/$1');
    $routes->post('articles/publish/(:num)', 'Admin\Articles::publish/$1');
    $routes->post('articles/unpublish/(:num)', 'Admin\Articles::unpublish/$1');

    // カテゴリー管理
    $routes->get('categories', 'Admin\Categories::index');
    $routes->match(['GET', 'POST'], 'categories/add', 'Admin\Categories::add');
    $routes->match(['GET', 'POST'], 'categories/edit/(:num)', 'Admin\Categories::edit/$1');
    $routes->post('categories/delete/(:num)', 'Admin\Categories::delete/$1');
    $routes->post('categories/reorder', 'Admin\Categories::reorder');

    // ユーザー管理
    $routes->get('users', 'Admin\Users::index');
    $routes->match(['GET', 'POST'], 'users/add', 'Admin\Users::add');
    $routes->match(['GET', 'POST'], 'users/edit/(:num)', 'Admin\Users::edit/$1');
    $routes->match(['GET', 'POST'], 'users/password/(:num)', 'Admin\Users::password/$1');
    $routes->post('users/delete/(:num)', 'Admin\Users::delete/$1');

    // サイト設定
    $routes->match(['GET', 'POST'], 'settings', 'Admin\Settings::index');
});

// 公開ルート（可変セグメント — 全固定ルートの後に配置）
$routes->get('category/(:any)', 'Wiki::category/$1');
$routes->get('(:segment)/(:segment)', 'Wiki::article/$1/$2');

