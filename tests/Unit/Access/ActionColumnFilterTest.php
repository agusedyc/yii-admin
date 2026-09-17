<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Access;

use Agusedyc\Admin\Access\AccessService;
use Agusedyc\Admin\Access\ActionColumnFilter;
use Agusedyc\Admin\Tests\Support\ArrayAssignmentsStorage;
use Agusedyc\Admin\Tests\Support\ArrayItemsStorage;
use Agusedyc\Admin\Tests\Support\UserIdProviderStub;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final class ActionColumnFilterTest extends TestCase
{
    private function createFilter(): ActionColumnFilter
    {
        $manager = new Manager(new ArrayItemsStorage(), new ArrayAssignmentsStorage());
        $manager->addPermission(new Permission('post/update'));
        $manager->addPermission(new Permission('post/delete'));
        $manager->addRole(new Role('moderator'));
        $manager->addChild('moderator', 'post/update');
        $manager->addChild('moderator', 'post/delete');
        $manager->assign('moderator', 'u1');

        $service = new AccessService($manager, new UserIdProviderStub('u1'));

        return new ActionColumnFilter($service);
    }

    public function testFiltersTemplate(): void
    {
        $template = $this->createFilter()->filter(['update', 'delete', 'download'], 'post/index');

        self::assertSame('{update} {delete}', $template);
    }

    public function testMapFormUsesKeyAsRoute(): void
    {
        $template = $this->createFilter()->filter(
            ['update' => 'update', 'delete' => 'delete', 'drop' => 'delete'],
            'post/index',
        );

        self::assertSame('{update} {delete}', $template);
    }
}
