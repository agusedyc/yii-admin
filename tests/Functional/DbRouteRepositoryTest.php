<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Functional;

use Agusedyc\Admin\Repository\DbRouteRepository;
use Agusedyc\Admin\Repository\RouteRecord;

final class DbRouteRepositoryTest extends FunctionalTestCase
{
    private DbRouteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRouteSchema();
        $this->repository = new DbRouteRepository($this->db);
    }

    public function testInsertAndFind(): void
    {
        $this->repository->insert(new RouteRecord('post/index', '/post', 'GET', 'post', 'index', 1));

        $record = $this->repository->find('post/index');

        self::assertNotNull($record);
        self::assertSame('/post', $record->getPattern());
        self::assertSame('GET', $record->getMethods());
        self::assertSame('post', $record->getGroup());
        self::assertSame('index', $record->getAlias());
        self::assertTrue($record->isActive());
        self::assertTrue($this->repository->exists('post/index'));
        self::assertFalse($this->repository->exists('missing'));
        self::assertNull($this->repository->find('missing'));
    }

    public function testAllSortedByGroup(): void
    {
        $this->repository->insert(new RouteRecord('user/index', '/user', 'GET', 'user', 'index', 1));
        $this->repository->insert(new RouteRecord('admin/index', '/admin', 'GET', 'admin', 'index', 1));

        $groups = array_map(
            static fn (RouteRecord $record): string => $record->getGroup(),
            $this->repository->all(),
        );

        self::assertSame(['admin', 'user'], $groups);
    }

    public function testUpdateOperations(): void
    {
        $this->repository->insert(new RouteRecord('post/index', '/post', 'GET', 'post', 'index', 1));

        $this->repository->updateDetails('post/index', '/posts', 'GET,POST');
        $this->repository->setMeta('post/index', 'Post list', 'blog');
        $this->repository->setStatus('post/index', 0);

        $record = $this->repository->find('post/index');

        self::assertSame('/posts', $record->getPattern());
        self::assertSame('GET,POST', $record->getMethods());
        self::assertSame('Post list', $record->getAlias());
        self::assertSame('blog', $record->getGroup());
        self::assertFalse($record->isActive());
    }

    public function testRemove(): void
    {
        $this->repository->insert(new RouteRecord('post/index', '/post', 'GET', 'post', 'index', 1));
        $this->repository->remove('post/index');

        self::assertFalse($this->repository->exists('post/index'));
    }
}
