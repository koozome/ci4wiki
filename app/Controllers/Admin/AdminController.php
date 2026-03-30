<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class AdminController extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        if (! auth()->loggedIn()) {
            header('Location: ' . site_url('login'));
            exit;
        }

        if (! auth()->user()->can('admin.access')) {
            header('Location: ' . site_url('/'));
            exit;
        }
    }

    protected function hasPermission(string $permission): bool
    {
        return auth()->user()->can($permission);
    }

    protected function requirePermission(string $permission): void
    {
        if (! $this->hasPermission($permission)) {
            header('Location: ' . site_url('admin'));
            exit;
        }
    }
}
