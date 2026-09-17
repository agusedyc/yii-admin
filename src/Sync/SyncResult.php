<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Sync;

/**
 * Result of a route synchronization run.
 */
final class SyncResult
{
    /** @var string[] */
    private array $addedPermissions = [];

    /** @var string[] */
    private array $addedRoutes = [];

    /** @var string[] */
    private array $addedGroups = [];

    /** @var string[] */
    private array $disabled = [];

    /** @var string[] */
    private array $removedPermissions = [];

    public function addPermissionName(string $name): void
    {
        $this->addedPermissions[] = $name;
    }

    public function addRouteName(string $name): void
    {
        $this->addedRoutes[] = $name;
    }

    public function addGroupName(string $name): void
    {
        $this->addedGroups[] = $name;
    }

    public function disableRoute(string $name): void
    {
        $this->disabled[] = $name;
    }

    public function removePermissionName(string $name): void
    {
        $this->removedPermissions[] = $name;
    }

    /**
     * @return string[]
     */
    public function getAddedPermissions(): array
    {
        return $this->addedPermissions;
    }

    /**
     * @return string[]
     */
    public function getAddedRoutes(): array
    {
        return $this->addedRoutes;
    }

    /**
     * @return string[]
     */
    public function getAddedGroups(): array
    {
        return $this->addedGroups;
    }

    /**
     * @return string[]
     */
    public function getDisabled(): array
    {
        return $this->disabled;
    }

    /**
     * @return string[]
     */
    public function getRemovedPermissions(): array
    {
        return $this->removedPermissions;
    }

    public function isEmpty(): bool
    {
        return $this->addedPermissions === []
            && $this->addedRoutes === []
            && $this->addedGroups === []
            && $this->disabled === []
            && $this->removedPermissions === [];
    }
}
