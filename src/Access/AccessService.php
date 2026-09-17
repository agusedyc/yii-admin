<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Access;

use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\Support\Matcher;

use function explode;
use function trim;

use Yiisoft\Rbac\ManagerInterface;

/**
 * Authorization helper: the Yii3 equivalent of the Yii2 `Mimin::checkRoute()`.
 *
 * A route is considered accessible when:
 *  - it is in the allowed routes list, or
 *  - the user has the corresponding RBAC permission, or
 *  - (non-strict mode) the user has the parent group permission.
 */
final class AccessService
{
    /**
     * @param string[] $allowRoutes
     */
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly UserIdProviderInterface $userIdProvider,
        private readonly ?RouteRepositoryInterface $routeRepository = null,
        private readonly array $allowRoutes = [],
        private readonly bool $checkDisabledRoutes = true,
    ) {
    }

    public function getUserId(): int|string|null
    {
        return $this->userIdProvider->getUserId();
    }

    public function isGuest(): bool
    {
        return $this->getUserId() === null;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function can(string $permission, array $parameters = []): bool
    {
        return $this->manager->userHasPermission($this->getUserId(), $permission, $parameters);
    }

    public function isAllowed(string $value): bool
    {
        return Matcher::matchesAny($value, $this->allowRoutes);
    }

    public function checkRoute(string $route, bool $strict = false): bool
    {
        $route = self::normalizeRoute($route);

        if ($route === '') {
            return false;
        }

        if (!$strict && $this->isAllowed($route)) {
            return true;
        }

        if ($this->checkDisabledRoutes && $this->routeRepository instanceof \Agusedyc\Admin\Repository\RouteRepositoryInterface) {
            $record = $this->routeRepository->find($route);
            if ($record instanceof \Agusedyc\Admin\Repository\RouteRecord && !$record->isActive()) {
                return false;
            }
        }

        if ($this->can($route)) {
            return true;
        }

        if (!$strict) {
            $group = self::groupOf($route);
            if ($group !== '' && $group !== $route && $this->can($group)) {
                return true;
            }
        }

        return false;
    }

    public static function normalizeRoute(string $route): string
    {
        return trim($route, '/');
    }

    public static function groupOf(string $route): string
    {
        return explode('/', $route)[0] ?? '';
    }
}
