<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Support\RequestParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Role;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class UpdateAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly CurrentRoute $currentRoute,
        private readonly UrlGeneratorInterface $url,
        private readonly ResponseHelper $response,
        private readonly FlashInterface $flash,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $oldName = $this->currentRoute->getArgument('name');

        if ($oldName === null || !$this->manager->getRole($oldName) instanceof \Yiisoft\Rbac\Role) {
            return $this->response->notFound();
        }

        $newName = RequestParser::string($request, 'name');
        $description = RequestParser::string($request, 'description') ?? '';

        if ($newName === null) {
            $this->flash->set('error', 'Nama role wajib diisi.');

            return $this->response->redirect($this->url->generate('admin/role/edit', ['name' => $oldName]));
        }

        if ($newName !== $oldName
            && ($this->manager->getRole($newName) instanceof \Yiisoft\Rbac\Role || $this->manager->getPermission($newName) instanceof \Yiisoft\Rbac\Permission)) {
            $this->flash->set('error', sprintf('Role atau permission "%s" sudah ada.', $newName));

            return $this->response->redirect($this->url->generate('admin/role/edit', ['name' => $oldName]));
        }

        $this->manager->updateRole(
            $oldName,
            (new Role($newName))->withDescription($description),
        );

        $this->flash->set('success', sprintf('Role "%s" berhasil diperbarui.', $newName));

        return $this->response->redirect($this->url->generate('admin/role/view', ['name' => $newName]));
    }
}
