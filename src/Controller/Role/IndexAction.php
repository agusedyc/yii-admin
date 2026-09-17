<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\View\AdminViewRenderer;

use function count;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;

final class IndexAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly ItemsStorageInterface $itemsStorage,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly AdminViewRenderer $view,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $roles = $this->itemsStorage->getRoles();
        $permissionCounts = [];
        foreach ($roles as $name => $role) {
            $permissionCounts[$name] = count($this->manager->getPermissionsByRoleName($name));
        }

        return $this->view->render('role/index', [
            'roles' => $roles,
            'permissionCounts' => $permissionCounts,
            'permissionCount' => count($this->itemsStorage->getPermissions()),
            'activeRouteCount' => count($this->activeRoutes()),
        ]);
    }

    private function activeRoutes(): array
    {
        $active = [];

        foreach ($this->routeRepository->all() as $record) {
            if ($record->isActive()) {
                $active[] = $record;
            }
        }

        return $active;
    }
}
