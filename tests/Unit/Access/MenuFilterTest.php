<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Access;

use Agusedyc\Admin\Access\AccessService;
use Agusedyc\Admin\Access\MenuFilter;
use Agusedyc\Admin\Tests\Support\ArrayAssignmentsStorage;
use Agusedyc\Admin\Tests\Support\ArrayItemsStorage;
use Agusedyc\Admin\Tests\Support\UserIdProviderStub;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final class MenuFilterTest extends TestCase
{
    private function createService(string $userId = 'u1'): AccessService
    {
        $manager = new Manager(new ArrayItemsStorage(), new ArrayAssignmentsStorage());
        $manager->addPermission(new Permission('post/index'));
        $manager->addPermission(new Permission('post/create'));
        $manager->addRole(new Role('author'));
        $manager->addChild('author', 'post/index');
        $manager->assign('author', $userId);

        return new AccessService($manager, new UserIdProviderStub($userId), null, ['home']);
    }

    public function testFiltersNestedMenu(): void
    {
        $filter = new MenuFilter($this->createService());

        $menu = [
            ['label' => 'Home', 'url' => 'home'],
            [
                'label' => 'Posts',
                'items' => [
                    ['label' => 'List', 'url' => ['post/index']],
                    ['label' => 'Create', 'url' => ['post/create']],
                ],
            ],
        ];

        $result = $filter->filter($menu);

        self::assertCount(2, $result);
        self::assertSame('Posts', $result[1]['label']);
        self::assertCount(1, $result[1]['items']);
        self::assertSame('List', $result[1]['items'][0]['label']);
    }

    public function testMenuWithoutUrlIsKept(): void
    {
        $filter = new MenuFilter($this->createService());

        $result = $filter->filter([
            ['label' => 'Header'],
            ['label' => 'Create', 'url' => ['post/create']],
        ]);

        self::assertCount(1, $result);
        self::assertSame('Header', $result[0]['label']);
    }

    public function testEmptyGroupIsRemoved(): void
    {
        $filter = new MenuFilter($this->createService());

        $result = $filter->filter([
            [
                'label' => 'Posts',
                'items' => [
                    ['label' => 'Create', 'url' => ['post/create']],
                ],
            ],
        ]);

        self::assertSame([], $result);
    }
}
