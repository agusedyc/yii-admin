<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Route;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class ToggleAction
{
    public function __construct(
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly CurrentRoute $currentRoute,
        private readonly UrlGeneratorInterface $url,
        private readonly ResponseHelper $response,
        private readonly FlashInterface $flash,
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
        $this->routeRepository->setStatus($name, $record->isActive() ? 0 : 1);
        $this->flash->set('success', sprintf('Status route "%s" diperbarui.', $name));

        return $this->response->redirect($this->url->generate('admin/route/index'));
    }
}
