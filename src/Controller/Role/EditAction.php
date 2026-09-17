<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\View\AdminViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;

final class EditAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
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
        $role = $this->manager->getRole($name);
        if (!$role instanceof \Yiisoft\Rbac\Role) {
            return $this->response->notFound();
        }

        return $this->view->render('role/edit', [
            'role' => $role,
        ]);
    }
}
