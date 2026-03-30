<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url', 'html', 'markdown', 'wiki'];

    protected array $siteSettings = [];
    protected ?array $authUser    = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->siteSettings = model(SettingModel::class)->getAll();
        if (auth()->loggedIn()) {
            $this->authUser = auth()->user()->toArray();
        }
    }

    protected function render(string $view, array $data = []): string
    {
        $data['siteSettings'] = $this->siteSettings;
        $data['authUser']     = $this->authUser;
        return view($view, $data);
    }
}
