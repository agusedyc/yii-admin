<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Route;

/**
 * Supplies the list of application routes that should be available as RBAC permissions.
 */
interface RouteProviderInterface
{
    /**
     * @return RouteInfo[]
     */
    public function getRoutes(): array;
}
