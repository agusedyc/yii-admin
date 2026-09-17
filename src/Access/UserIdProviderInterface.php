<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Access;

/**
 * Resolves the current user ID for access checks. `null` means a guest.
 */
interface UserIdProviderInterface
{
    public function getUserId(): int|string|null;
}
