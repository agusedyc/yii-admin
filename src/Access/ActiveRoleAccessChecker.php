<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Access;

use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Session\SessionInterface;

final class ActiveRoleAccessChecker implements AccessCheckerInterface
{
    public const SESSION_KEY = 'active_admin_role';

    public function __construct(
        private readonly AccessCheckerInterface $innerChecker,
        private readonly SessionInterface $session,
        private readonly ManagerInterface $manager
    ) {
    }

    public function userHasPermission(
        $userId,
        string $permissionName,
        array $parameters = []
    ): bool {
        $activeRole = $this->session->get(self::SESSION_KEY);

        // If no active role selected, fallback to default behavior
        if ($activeRole === null) {
            return $this->innerChecker->userHasPermission($userId, $permissionName, $parameters);
        }

        // Verify if user actually has the requested active role
        if ($userId === null) {
            return $this->innerChecker->userHasPermission($userId, $permissionName, $parameters);
        }

        $assignments = $this->manager->getRolesByUserId($userId);
        if (!isset($assignments[(string)$activeRole])) {
            return $this->innerChecker->userHasPermission($userId, $permissionName, $parameters);
        }

        $activeRolePermissions = $this->manager->getPermissionsByRoleName((string)$activeRole);
        // If the permission is not part of the active role (and not the active role itself), deny
        if ($permissionName !== $activeRole && !isset($activeRolePermissions[$permissionName])) {
            return false;
        }

        return $this->innerChecker->userHasPermission($userId, $permissionName, $parameters);
    }
}
