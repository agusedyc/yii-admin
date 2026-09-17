<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Route;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\Support\RequestParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class UpdateAction
{
    public function __construct(
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly CurrentRoute $currentRoute,
        private readonly UrlGeneratorInterface $url,
        private readonly ResponseHelper $response,
        private readonly FlashInterface $flash,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $name = $this->currentRoute->getArgument('name');

        if ($name === null || !$this->routeRepository->find($name) instanceof \Agusedyc\Admin\Repository\RouteRecord) {
            return $this->response->notFound();
        }

        $alias = RequestParser::string($request, 'alias');
        $group = RequestParser::string($request, 'group');

        if ($alias === null || $group === null) {
            $this->flash->set('error', 'Alias dan grup wajib diisi.');

            return $this->response->redirect(
                $this->url->generate('admin/route/edit', ['name' => $name]),
            );
        }

        $this->routeRepository->setMeta($name, $alias, $group);

        $this->flash->set('success', 'Route diperbarui.');

        return $this->response->redirect($this->url->generate('admin/route/index'));
    }
}
