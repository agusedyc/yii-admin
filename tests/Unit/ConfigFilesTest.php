<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit;

use function array_keys;
use function dirname;

use PHPUnit\Framework\TestCase;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

final class ConfigFilesTest extends TestCase
{
    private function params(array $overrides = []): array
    {
        return [
            'agusedyc/yii-admin' => array_merge([
                'enabled' => true,
                'path' => '/admin',
                'namePrefix' => 'admin/',
                'allowRoutes' => ['admin/*'],
                'ignoreRouteNames' => [],
                'ignoreRoutePatterns' => [],
                'groupPermissions' => true,
            ], $overrides),
        ];
    }

    public function testParamsConfig(): void
    {
        $config = require dirname(__DIR__, 2) . '/config/params.php';

        self::assertIsArray($config);
        self::assertArrayHasKey('agusedyc/yii-admin', $config);
        self::assertArrayHasKey('yiisoft/yii-console', $config);
    }

    public function testDiConfig(): void
    {
        $params = $this->params();
        $config = require dirname(__DIR__, 2) . '/config/di.php';

        self::assertIsArray($config);
        self::assertNotEmpty($config);
    }

    public function testRoutesConfig(): void
    {
        $params = $this->params();
        $config = require dirname(__DIR__, 2) . '/config/routes.php';

        self::assertIsArray($config);
        self::assertNotEmpty($config);
    }

    public function testRoutesConfigCanBeDisabled(): void
    {
        $params = $this->params(['enabled' => false]);

        $config = require dirname(__DIR__, 2) . '/config/routes.php';

        self::assertSame([], $config);
    }

    public function testRoutesContainNamedRoutes(): void
    {
        $params = $this->params();
        $config = require dirname(__DIR__, 2) . '/config/routes.php';

        $collector = new RouteCollector();
        $collector->addRoute(...$config);
        $collection = new RouteCollection($collector);

        $names = array_keys($collection->getRoutes());
        self::assertContains('admin/route/index', $names);
        self::assertContains('admin/role/index', $names);
        self::assertContains('admin/role/permission', $names);
    }
}
