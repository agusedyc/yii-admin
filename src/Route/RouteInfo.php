<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Route;

use function implode;

/**
 * Read-only representation of an application route exposed as an RBAC permission.
 */
final class RouteInfo
{
    /**
     * @param string[] $methods
     */
    public function __construct(
        private readonly string $name,
        private readonly string $pattern,
        private readonly array $methods,
        private readonly string $group,
        private readonly string $alias,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getMethodsAsString(): string
    {
        return implode(',', $this->methods);
    }
}
