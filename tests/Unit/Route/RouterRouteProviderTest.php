<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Route;

use Agusedyc\Admin\Route\RouterRouteProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

final class RouterRouteProviderTest extends TestCase
{
    private function createCollection(): RouteCollection
    {
        $collector = new RouteCollector();
        $collector->addRoute(
            Route::get('/')->name('home'),
            Route::get('/post')->name('post/index'),
            Route::post('/post')->name('post/create'),
            Group::create('/admin')->routes(
                Route::get('/users')->name('admin/user/index'),
            ),
        );

        return new RouteCollection($collector);
    }

    public function testCollectsRoutes(): void
    {
        $provider = new RouterRouteProvider($this->createCollection());
        $routes = $provider->getRoutes();

        $names = array_map(static fn (\Agusedyc\Admin\Route\RouteInfo $route): string => $route->getName(), $routes);

        self::assertContains('home', $names);
        self::assertContains('post/index', $names);
        self::assertContains('admin/user/index', $names);
    }

    public function testGroupsAndAliases(): void
    {
        $provider = new RouterRouteProvider($this->createCollection());

        $indexed = [];
        foreach ($provider->getRoutes() as $route) {
            $indexed[$route->getName()] = $route;
        }

        self::assertSame('post', $indexed['post/index']->getGroup());
        self::assertSame('index', $indexed['post/index']->getAlias());

        self::assertSame('admin', $indexed['admin/user/index']->getGroup());
        self::assertSame('user/index', $indexed['admin/user/index']->getAlias());

        self::assertSame('general', $indexed['home']->getGroup());
    }

    public function testIgnoreRoutes(): void
    {
        $provider = new RouterRouteProvider($this->createCollection(), ['admin/*']);
        $names = array_map(static fn (\Agusedyc\Admin\Route\RouteInfo $route): string => $route->getName(), $provider->getRoutes());

        self::assertNotContains('admin/user/index', $names);
        self::assertContains('post/index', $names);
    }

    public function testMethodsAreCollected(): void
    {
        $provider = new RouterRouteProvider($this->createCollection());
        $indexed = [];
        foreach ($provider->getRoutes() as $route) {
            $indexed[$route->getName()] = $route;
        }

        self::assertSame(['GET'], $indexed['post/index']->getMethods());
    }
}
