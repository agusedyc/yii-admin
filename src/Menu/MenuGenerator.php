<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Menu;

use Agusedyc\Admin\Access\ActiveRoleAccessChecker;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;

final class MenuGenerator
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly SessionInterface $session,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RouteRepositoryInterface $routeRepository
    ) {
    }

    public function generate(): array
    {
        $activeRoleName = $this->session->get(ActiveRoleAccessChecker::SESSION_KEY);
        if ($activeRoleName === null) {
            return [];
        }

        $permissions = $this->manager->getPermissionsByRoleName((string)$activeRoleName);
        $routes = $this->routeRepository->all();

        $groups = [];
        $general = [];

        foreach ($routes as $route) {
            if (!$route->isActive()) {
                continue;
            }

            $name = $route->getName();
            if (!isset($permissions[$name])) {
                continue;
            }

            $group = $route->getGroup();

            // Generate URL safely
            try {
                // We assume routes generated in admin menu might not need params by default,
                // or we just use their pattern/name. We'll use name if it has no params, otherwise fallback.
                // Note: UrlGeneratorInterface might throw if params are missing.
                // For a generic menu, if it throws, we can skip or use '#'
                $url = $this->urlGenerator->generate($name);
            } catch (\Exception $e) {
                $url = '#';
            }

            $item = [
                'label' => $route->getAlias() ?: $name,
                'url' => $url,
                // Add attributes for HTMX navigation if needed
                'linkOptions' => [
                    'hx-get' => $url,
                    'hx-target' => 'body',
                ],
            ];

            if ($group === '' || $group === 'general') {
                $general[] = $item;
            } else {
                if (!isset($groups[$group])) {
                    $groups[$group] = [
                        'label' => ucfirst($group),
                        'items' => [],
                    ];
                }
                $groups[$group]['items'][] = $item;
            }
        }

        $menus = [];
        foreach ($general as $item) {
            $menus[] = $item;
        }
        foreach ($groups as $group) {
            if (!empty($group['items'])) {
                $menus[] = $group;
            }
        }

        return $menus;
    }
}
