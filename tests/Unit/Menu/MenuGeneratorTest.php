<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Menu;

use Agusedyc\Admin\Access\ActiveRoleAccessChecker;
use Agusedyc\Admin\Menu\MenuGenerator;
use Agusedyc\Admin\Repository\RouteRecord;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;

final class MenuGeneratorTest extends BaseTestCase
{
    public function testGenerateReturnsEmptyArrayWhenNoActiveRole(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $routeRepository = $this->createMock(RouteRepositoryInterface::class);

        $session->method('get')->with(ActiveRoleAccessChecker::SESSION_KEY)->willReturn(null);

        $generator = new MenuGenerator($manager, $session, $urlGenerator, $routeRepository);
        $this->assertSame([], $generator->generate());
    }

    public function testGenerateReturnsMenus(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $routeRepository = $this->createMock(RouteRepositoryInterface::class);

        $session->method('get')->with(ActiveRoleAccessChecker::SESSION_KEY)->willReturn('admin');
        $manager->method('getPermissionsByRoleName')->with('admin')->willReturn([
            'admin/user/index' => new Permission('admin/user/index'),
        ]);

        $record = new RouteRecord('admin/user/index', '/admin/user', 'GET', 'user', 'User Index', 1, 0, 0);
        $routeRepository->method('all')->willReturn([$record]);

        $urlGenerator->method('generate')->with('admin/user/index')->willReturn('/admin/user');

        $generator = new MenuGenerator($manager, $session, $urlGenerator, $routeRepository);
        $menus = $generator->generate();

        $this->assertCount(1, $menus);
        $this->assertSame('User', $menus[0]['label']);
        $this->assertCount(1, $menus[0]['items']);
        $this->assertSame('User Index', $menus[0]['items'][0]['label']);
        $this->assertSame('/admin/user', $menus[0]['items'][0]['url']);
    }
}
