<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Access;

use Agusedyc\Admin\Access\AccessService;
use Agusedyc\Admin\Repository\RouteRecord;
use Agusedyc\Admin\Tests\Support\ArrayAssignmentsStorage;
use Agusedyc\Admin\Tests\Support\ArrayItemsStorage;
use Agusedyc\Admin\Tests\Support\FakeRouteRepository;
use Agusedyc\Admin\Tests\Support\UserIdProviderStub;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final class AccessServiceTest extends TestCase
{
    private function createManager(): Manager
    {
        $manager = new Manager(new ArrayItemsStorage(), new ArrayAssignmentsStorage());

        $manager->addPermission(new Permission('admin'));
        $manager->addPermission(new Permission('admin/index'));
        $manager->addPermission(new Permission('admin/create'));

        $manager->addRole(new Role('editor'));
        $manager->addChild('editor', 'admin/index');

        $manager->assign('editor', 'u1');

        return $manager;
    }

    public function testExactPermission(): void
    {
        $service = new AccessService($this->createManager(), new UserIdProviderStub('u1'));

        self::assertTrue($service->checkRoute('/admin/index'));
        self::assertFalse($service->checkRoute('admin/create'));
    }

    public function testAllowRoutesShortCircuit(): void
    {
        $service = new AccessService(
            $this->createManager(),
            new UserIdProviderStub(null),
            null,
            ['public/*'],
        );

        self::assertTrue($service->checkRoute('public/page'));
        self::assertFalse($service->checkRoute('public/page', strict: true));
    }

    public function testGroupFallback(): void
    {
        $manager = new Manager(new ArrayItemsStorage(), new ArrayAssignmentsStorage());
        $manager->addPermission(new Permission('reports'));
        $manager->addPermission(new Permission('reports/daily'));
        $manager->addRole(new Role('reporter'));
        $manager->addChild('reporter', 'reports');
        $manager->assign('reporter', 'u1');

        $service = new AccessService($manager, new UserIdProviderStub('u1'));

        self::assertTrue($service->checkRoute('reports/daily'));
        self::assertFalse($service->checkRoute('reports/daily', strict: true));
    }

    public function testDisabledRouteIsDenied(): void
    {
        $repository = new FakeRouteRepository([
            new RouteRecord('admin/index', '/admin/index', 'GET', 'admin', 'index', 0),
        ]);

        $service = new AccessService(
            $this->createManager(),
            new UserIdProviderStub('u1'),
            $repository,
        );

        self::assertFalse($service->checkRoute('admin/index'));
    }

    public function testGuestIsDenied(): void
    {
        $service = new AccessService($this->createManager(), new UserIdProviderStub(null));

        self::assertTrue($service->isGuest());
        self::assertFalse($service->checkRoute('admin/index'));
    }
}
