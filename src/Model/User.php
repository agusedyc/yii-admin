<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Model;

use Yiisoft\Auth\IdentityInterface;

final class User implements IdentityInterface
{
    public function __construct(
        public readonly string $id,
        public string $username,
        public string $email
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
