<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Support;

use Yiisoft\Session\Flash\FlashInterface;

final class FlashStub implements FlashInterface
{
    /** @var array<string, mixed> */
    private array $messages = [];

    public function get(string $key)
    {
        return $this->messages[$key] ?? null;
    }

    public function getAll(): array
    {
        return $this->messages;
    }

    public function set(string $key, $value = true, bool $removeAfterAccess = true): void
    {
        $this->messages[$key] = $value;
    }

    public function add(string $key, $value = true, bool $removeAfterAccess = true): void
    {
        $this->messages[$key] ??= [];

        $this->messages[$key][] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->messages[$key]);
    }

    public function removeAll(): void
    {
        $this->messages = [];
    }

    public function has(string $key): bool
    {
        return isset($this->messages[$key]);
    }
}
