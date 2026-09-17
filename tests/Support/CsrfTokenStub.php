<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Support;

use Yiisoft\Csrf\CsrfTokenInterface;

final class CsrfTokenStub implements CsrfTokenInterface
{
    public function __construct(private readonly string $value = 'test-token')
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function validate(string $token): bool
    {
        return $this->value === $token;
    }
}
