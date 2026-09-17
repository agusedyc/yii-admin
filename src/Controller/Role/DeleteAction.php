<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Http\ResponseHelper;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class DeleteAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly CurrentRoute $currentRoute,
        private readonly UrlGeneratorInterface $url,
        private readonly ResponseHelper $response,
        private readonly FlashInterface $flash,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $name = $this->currentRoute->getArgument('name');
        if ($name !== null) {
            $this->manager->removeRole($name);
            $this->flash->set('success', sprintf('Role "%s" dihapus.', $name));
        }

        return $this->response->redirect($this->url->generate('admin/role/index'));
    }
}
