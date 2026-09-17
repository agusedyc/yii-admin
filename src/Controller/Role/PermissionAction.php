<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Support\RequestParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * Toggles a permission on/off for a role and returns a JSON response.
 */
final class PermissionAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly CurrentRoute $currentRoute,
        private readonly ResponseHelper $response,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $roleName = $this->currentRoute->getArgument('name');
        $permissionName = RequestParser::string($request, 'permission');

        if ($roleName === null || $permissionName === null) {
            return $this->response->json(['ok' => false, 'message' => 'Parameter role / permission tidak lengkap.'], 400);
        }

        if (!$this->manager->getRole($roleName) instanceof \Yiisoft\Rbac\Role) {
            return $this->response->json(['ok' => false, 'message' => 'Role tidak ditemukan.'], 404);
        }

        if (!$this->manager->getPermission($permissionName) instanceof \Yiisoft\Rbac\Permission) {
            return $this->response->json(['ok' => false, 'message' => 'Permission tidak ditemukan.'], 404);
        }

        $granted = !$this->manager->hasChild($roleName, $permissionName);

        if ($granted) {
            if (!$this->manager->canAddChild($roleName, $permissionName)) {
                return $this->response->json(['ok' => false, 'message' => 'Tidak dapat menambahkan permission.'], 422);
            }

            $this->manager->addChild($roleName, $permissionName);
        } else {
            $this->manager->removeChild($roleName, $permissionName);
        }

        return $this->response->json([
            'ok' => true,
            'granted' => $granted,
            'permission' => $permissionName,
            'message' => $granted ? 'Permission ditambahkan.' : 'Permission dihapus.',
        ]);
    }
}
