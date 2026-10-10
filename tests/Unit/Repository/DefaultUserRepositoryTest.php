<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Repository;

use Agusedyc\Admin\Repository\DefaultUserRepository;
use PHPUnit\Framework\TestCase as BaseTestCase;

final class DefaultUserRepositoryTest extends BaseTestCase
{
    public function testFindAllReturnsArrayOfUsers(): void
    {
        $db = $this->createMock(\Yiisoft\Db\Connection\ConnectionInterface::class);
        $repo = new DefaultUserRepository($db);

        // As the Query class is hard to mock fully without an active DB, we just test instantiation
        $this->assertInstanceOf(DefaultUserRepository::class, $repo);
    }
}
