<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Sync;

use Agusedyc\Admin\Route\RouterRouteProvider;
use Agusedyc\Admin\Sync\RouteSynchronizer;
use Agusedyc\Admin\Tests\Support\ArrayAssignmentsStorage;
use Agusedyc\Admin\Tests\Support\ArrayItemsStorage;
use Agusedyc\Admin\Tests\Support\FakeRouteRepository;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\Manager;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

final class RouteSynchronizerTest extends TestCase
{
    private Manager $manager;
    private ArrayItemsStorage $itemsStorage;
    private FakeRouteRepository $repository;

    protected function setUp(): void
    {
        $this->itemsStorage = new ArrayItemsStorage();
        $this->manager = new Manager($this->itemsStorage, new ArrayAssignmentsStorage());
        $this->repository = new FakeRouteRepository();
    }

    private function createSynchronizer(array $routeNames, bool $groupPermissions = true): RouteSynchronizer
    {
        $collector = new RouteCollector();
        foreach ($routeNames as $name) {
            $collector->addRoute(Route::get('/' . $name)->name($name));
        }

        return new RouteSynchronizer(
            $this->manager,
            new RouterRouteProvider(new RouteCollection($collector)),
            $this->repository,
            $groupPermissions,
        );
    }

    public function testCreatesPermissionsAndGroups(): void
    {
        $result = $this->createSynchronizer(['post/index', 'post/create'])->sync();

        self::assertCount(2, $result->getAddedPermissions());
        self::assertNotNull($this->manager->getPermission('post/index'));
        self::assertNotNull($this->manager->getPermission('post'));
        self::assertTrue($this->manager->hasChild('post', 'post/index'));
        self::assertTrue($this->manager->hasChild('post', 'post/create'));
    }

    public function testSecondSyncIsIdempotent(): void
    {
        $this->createSynchronizer(['post/index'])->sync();
        $result = $this->createSynchronizer(['post/index'])->sync();

        self::assertSame([], $result->getAddedPermissions());
        self::assertSame([], $result->getAddedGroups());
        self::assertCount(2, $this->itemsStorage->getPermissions());
    }

    public function testGroupPermissionsCanBeDisabled(): void
    {
        $this->createSynchronizer(['post/index'], false)->sync();

        self::assertNull($this->manager->getPermission('post'));
        self::assertNotNull($this->manager->getPermission('post/index'));
    }

    public function testMissingRouteIsDisabled(): void
    {
        $this->createSynchronizer(['post/index', 'post/create'])->sync();

        $result = $this->createSynchronizer(['post/index'])->sync();

        self::assertContains('post/create', $result->getDisabled());
        self::assertFalse($this->repository->find('post/create')->isActive());
        // The permission itself is kept for existing assignments.
        self::assertNotNull($this->manager->getPermission('post/create'));
    }

    public function testReenablesReturningRoute(): void
    {
        $this->createSynchronizer(['post/index'])->sync();
        $this->createSynchronizer([])->sync();
        self::assertFalse($this->repository->find('post/index')->isActive());

        $this->createSynchronizer(['post/index'])->sync();

        self::assertTrue($this->repository->find('post/index')->isActive());
    }
}
