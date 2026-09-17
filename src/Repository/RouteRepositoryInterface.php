<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Repository;

/**
 * Stores display metadata and enabled/disabled state of route permissions.
 */
interface RouteRepositoryInterface
{
    /**
     * @return RouteRecord[]
     */
    public function all(): array;

    public function find(string $name): ?RouteRecord;

    public function exists(string $name): bool;

    public function insert(RouteRecord $record): void;

    /**
     * Updates route technical details, keeping user defined alias/group and status.
     */
    public function updateDetails(string $name, string $pattern, string $methods): void;

    public function setStatus(string $name, int $status): void;

    public function setMeta(string $name, ?string $alias = null, ?string $group = null): void;

    public function remove(string $name): void;
}
