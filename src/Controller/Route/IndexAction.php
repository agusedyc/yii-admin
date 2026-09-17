<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Route;

use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\View\AdminViewRenderer;
use Psr\Http\Message\ResponseInterface;

final class IndexAction
{
    public function __construct(
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly AdminViewRenderer $view,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        return $this->view->render('route/index', [
            'records' => $this->routeRepository->all(),
        ]);
    }
}
