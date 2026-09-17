<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Route;

use Agusedyc\Admin\Support\Matcher;

use function explode;
use function is_array;
use function is_string;
use function preg_match;
use function str_contains;
use function trim;

use Yiisoft\Router\RouteCollectionInterface;

/**
 * Provides routes from the Yii3 router route collection.
 *
 * Permission names are equal to Yii3 route names, so access checks in the application
 * are a direct lookup: `CurrentRoute::getName()` -> RBAC permission name.
 */
final class RouterRouteProvider implements RouteProviderInterface
{
    /**
     * @param string[] $ignoreRouteNames
     * @param string[] $ignoreRoutePatterns
     */
    public function __construct(
        private readonly RouteCollectionInterface $routeCollection,
        private readonly array $ignoreRouteNames = [],
        private readonly array $ignoreRoutePatterns = [],
    ) {
    }

    public function getRoutes(): array
    {
        $result = [];

        foreach ($this->routeCollection->getRoutes() as $key => $route) {
            $rawName = $route->getData('name');
            $name = is_string($rawName) && $rawName !== '' ? $rawName : (string) $key;

            if ($name === '') {
                continue;
            }

            if (Matcher::matchesAny($name, $this->ignoreRouteNames)) {
                continue;
            }

            $pattern = (string) $route->getData('pattern');

            if ($pattern !== '' && Matcher::matchesAny($pattern, $this->ignoreRoutePatterns)) {
                continue;
            }

            if (isset($result[$name])) {
                continue;
            }

            $methods = $route->getData('methods');
            if (!is_array($methods)) {
                $methods = [];
            }

            /** @var string[] $methods */
            $result[$name] = new RouteInfo(
                $name,
                $pattern,
                array_values($methods),
                $this->deriveGroup($name, $pattern),
                $this->deriveAlias($name),
            );
        }

        return array_values($result);
    }

    private function deriveGroup(string $name, string $pattern): string
    {
        if ($this->isSyntheticName($name)) {
            $segment = explode('/', trim($pattern, '/'))[0] ?? '';

            return $segment !== '' ? $segment : 'general';
        }

        if (str_contains($name, '/')) {
            return explode('/', $name)[0];
        }

        return 'general';
    }

    private function deriveAlias(string $name): string
    {
        if ($this->isSyntheticName($name)) {
            return $name;
        }

        if (str_contains($name, '/')) {
            $parts = explode('/', $name);
            array_shift($parts);

            return implode('/', $parts);
        }

        return $name;
    }

    /**
     * Detects a name generated automatically by {@see \Yiisoft\Router\Route::getData()} for unnamed routes
     * (e.g. `GET /foo/bar`).
     */
    private function isSyntheticName(string $name): bool
    {
        return (bool) preg_match('/^[A-Z]+ .*/', $name);
    }
}
