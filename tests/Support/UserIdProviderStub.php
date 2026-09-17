<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Support;

use Agusedyc\Admin\Access\UserIdProviderInterface;

final class UserIdProviderStub implements UserIdProviderInterface
{
    public function __construct(private int|string|null $userId)
    {
    }

    public function setUserId(int|string|null $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserId(): int|string|null
    {
        return $this->userId;
    }
}
