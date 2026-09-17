<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Access\AccessService;
use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\View\AdminViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;

final class ViewAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly ItemsStorageInterface $itemsStorage,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly CurrentRoute $currentRoute,
        private readonly AdminViewRenderer $view,
        private readonly ResponseHelper $response,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $roleName = $this->currentRoute->getArgument('name');
        if ($roleName === null) {
            return $this->response->notFound();
        }
        $role = $this->manager->getRole($roleName);
        if (!$role instanceof \Yiisoft\Rbac\Role) {
            return $this->response->notFound();
        }
        $aliases = [];
        foreach ($this->routeRepository->all() as $record) {
            $aliases[$record->getName()] = $record->getAlias();
        }
        $groups = [];
        foreach ($this->itemsStorage->getPermissions() as $name => $permission) {
            if ($name === $roleName) {
                continue;
            }

            $group = AccessService::groupOf($name);
            $groups[$group][$name] = [
                'name' => $name,
                'alias' => $aliases[$name] ?? $name,
                'checked' => $this->manager->hasChild($roleName, $name),
            ];
        }

        return $this->view->render('role/view', [
            'role' => $role,
            'roleName' => $roleName,
            'groups' => $groups,
        ]);
    }
}
