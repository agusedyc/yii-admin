<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Sync;

use Agusedyc\Admin\Repository\RouteRecord;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\Route\RouteProviderInterface;

use function time;

use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;

/**
 * Synchronizes application routes with RBAC permissions and the {@see RouteRepositoryInterface}
 * route metadata table.
 */
final class RouteSynchronizer
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly RouteProviderInterface $routeProvider,
        private readonly RouteRepositoryInterface $repository,
        private readonly bool $groupPermissions = true,
        private readonly bool $purgeMissingPermissions = false,
    ) {
    }

    public function sync(): SyncResult
    {
        $result = new SyncResult();

        $routes = $this->routeProvider->getRoutes();

        /** @var array<string, \Agusedyc\Admin\Route\RouteInfo> $indexed */
        $indexed = [];
        foreach ($routes as $route) {
            $indexed[$route->getName()] = $route;
        }

        /** @var array<string, RouteRecord> $existing */
        $existing = [];
        foreach ($this->repository->all() as $record) {
            $existing[$record->getName()] = $record;
        }

        /** @var array<string, string[]> $groups */
        $groups = [];

        foreach ($indexed as $name => $route) {
            if (!$this->manager->getPermission($name) instanceof \Yiisoft\Rbac\Permission) {
                $this->manager->addPermission(
                    (new Permission($name))->withDescription($route->getAlias()),
                );
                $result->addPermissionName($name);
            }

            if (!isset($existing[$name])) {
                $this->repository->insert(
                    new RouteRecord(
                        $route->getName(),
                        $route->getPattern(),
                        $route->getMethodsAsString(),
                        $route->getGroup(),
                        $route->getAlias(),
                        1,
                        time(),
                        time(),
                    ),
                );
                $result->addRouteName($name);
            } elseif (!$existing[$name]->isActive()) {
                $this->repository->setStatus($name, 1);
                $result->addRouteName($name);
            } else {
                $this->repository->updateDetails(
                    $name,
                    $route->getPattern(),
                    $route->getMethodsAsString(),
                );
            }

            $groups[$route->getGroup()][$name] = $name;
        }

        $this->syncRemovedRoutes($existing, $indexed, $result);

        if ($this->groupPermissions) {
            $this->syncGroups($groups, $result);
        }

        return $result;
    }

    /**
     * @param array<string, RouteRecord> $existing
     * @param array<string, \Agusedyc\Admin\Route\RouteInfo> $indexed
     */
    private function syncRemovedRoutes(array $existing, array $indexed, SyncResult $result): void
    {
        foreach ($existing as $name => $record) {
            if (isset($indexed[$name])) {
                continue;
            }

            if ($record->isActive()) {
                $this->repository->setStatus($name, 0);
                $result->disableRoute($name);
            }

            if ($this->purgeMissingPermissions && $this->manager->getPermission($name) instanceof \Yiisoft\Rbac\Permission) {
                $this->manager->removePermission($name);
                $result->removePermissionName($name);
            }
        }
    }

    /**
     * Creates one parent permission per group and attaches route permissions as its children.
     *
     * @param array<string, string[]> $groups
     */
    private function syncGroups(array $groups, SyncResult $result): void
    {
        foreach ($groups as $group => $routeNames) {
            if ($group === '' || $group === 'general') {
                continue;
            }

            // A role with the same name takes priority; groups are only represented
            // by permissions to keep the UI predictable.
            if (!$this->manager->getPermission($group) instanceof \Yiisoft\Rbac\Permission && !$this->manager->getRole($group) instanceof \Yiisoft\Rbac\Role) {
                $this->manager->addPermission(new Permission($group));
                $result->addGroupName($group);
            }

            foreach ($routeNames as $child) {
                if ($group === $child || $this->manager->hasChild($group, $child)) {
                    continue;
                }

                if ($this->manager->canAddChild($group, $child)) {
                    $this->manager->addChild($group, $child);
                }
            }
        }
    }
}
