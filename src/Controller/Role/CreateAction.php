<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Support\RequestParser;
use Agusedyc\Admin\View\AdminViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Role;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class CreateAction
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly UrlGeneratorInterface $url,
        private readonly ResponseHelper $response,
        private readonly FlashInterface $flash,
        private readonly AdminViewRenderer $view,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->view->render('role/create');
        }

        $name = RequestParser::string($request, 'name');
        $description = RequestParser::string($request, 'description') ?? '';

        if ($name === null) {
            $this->flash->set('error', 'Nama role wajib diisi.');

            return $this->view->render('role/create');
        }

        if ($this->manager->getRole($name) instanceof \Yiisoft\Rbac\Role || $this->manager->getPermission($name) instanceof \Yiisoft\Rbac\Permission) {
            $this->flash->set('error', sprintf('Role atau permission "%s" sudah ada.', $name));

            return $this->view->render('role/create');
        }

        $this->manager->addRole((new Role($name))->withDescription($description));

        $this->flash->set('success', sprintf('Role "%s" berhasil dibuat.', $name));

        return $this->response->redirect($this->url->generate('admin/role/view', ['name' => $name]));
    }
}
