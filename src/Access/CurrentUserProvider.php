<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Access;

use Yiisoft\User\CurrentUser;

/**
 * Resolves the user ID from the Yii3 user component.
 */
final class CurrentUserProvider implements UserIdProviderInterface
{
    public function __construct(private readonly CurrentUser $currentUser)
    {
    }

    public function getUserId(): ?string
    {
        return $this->currentUser->getId();
    }
}
