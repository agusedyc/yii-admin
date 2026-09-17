<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Repository;

/**
 * Database representation of an RBAC route/permission metadata record.
 */
final class RouteRecord
{
    public function __construct(
        private readonly string $name,
        private readonly string $pattern,
        private readonly string $methods,
        private readonly string $group,
        private readonly string $alias,
        private readonly int $status,
        private readonly ?int $createdAt = null,
        private readonly ?int $updatedAt = null,
    ) {
    }

    /**
     * @psalm-param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (string) $row['name'],
            (string) ($row['pattern'] ?? ''),
            (string) ($row['methods'] ?? ''),
            (string) ($row['group_name'] ?? ''),
            (string) ($row['alias'] ?? ''),
            (int) ($row['status'] ?? 1),
            isset($row['created_at']) ? (int) $row['created_at'] : null,
            isset($row['updated_at']) ? (int) $row['updated_at'] : null,
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getMethods(): string
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }
}
