<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Functional;

use Agusedyc\Admin\Repository\DbRouteRepository;
use Agusedyc\Admin\Route\RouterRouteProvider;
use Agusedyc\Admin\Sync\RouteSynchronizer;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Manager;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

final class RouteSynchronizerDbTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createRbacSchema();
        $this->createRouteSchema();
    }

    private function createSynchronizer(array $routeNames): RouteSynchronizer
    {
        $manager = new Manager(
            new ItemsStorage($this->db),
            new AssignmentsStorage($this->db),
        );

        $collector = new RouteCollector();
        foreach ($routeNames as $name) {
            $collector->addRoute(Route::get('/' . $name)->name($name));
        }

        return new RouteSynchronizer(
            $manager,
            new RouterRouteProvider(new RouteCollection($collector)),
            new DbRouteRepository($this->db),
        );
    }

    public function testSyncPersistsPermissionsAndMetadata(): void
    {
        $result = $this->createSynchronizer(['post/index', 'post/create'])->sync();

        self::assertCount(2, $result->getAddedPermissions());

        $rows = $this->db->createQuery()->select('*')->from('yii_rbac_item')->all();
        self::assertCount(3, $rows); // post group + 2 routes

        $routes = $this->db->createQuery()->select('*')->from('admin_route')->all();
        self::assertCount(2, $routes);

        $children = $this->db->createQuery()->select('*')->from('yii_rbac_item_child')->all();
        self::assertCount(2, $children);
    }

    public function testMissingRouteIsDisabledInDatabase(): void
    {
        $this->createSynchronizer(['post/index', 'post/create'])->sync();
        $this->createSynchronizer(['post/index'])->sync();

        $row = $this->db->createQuery()
            ->select('*')
            ->from('admin_route')
            ->where(['name' => 'post/create'])
            ->one();

        self::assertIsArray($row);
        self::assertSame(0, (int) $row['status']);
    }
}
