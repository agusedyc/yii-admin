<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Route;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\View\AdminViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\CurrentRoute;

final class EditAction
{
    public function __construct(
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly CurrentRoute $currentRoute,
        private readonly AdminViewRenderer $view,
        private readonly ResponseHelper $response,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $name = $this->currentRoute->getArgument('name');
        if ($name === null) {
            return $this->response->notFound();
        }
        $record = $this->routeRepository->find($name);
        if (!$record instanceof \Agusedyc\Admin\Repository\RouteRecord) {
            return $this->response->notFound();
        }

        return $this->view->render('route/edit', [
            'record' => $record,
        ]);
    }
}
