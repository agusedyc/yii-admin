<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\User;

use Agusedyc\Admin\Model\User;
use Agusedyc\Admin\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Method;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;

final class UserController
{
    public function __construct(
        private readonly DataResponseFactoryInterface $responseFactory,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ManagerInterface $manager,
        private readonly ItemsStorageInterface $itemsStorage,
        private readonly AssignmentsStorageInterface $assignmentsStorage,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function index(): ResponseInterface
    {
        $users = $this->userRepository->findAll();

        return $this->responseFactory->createResponse([
            'users' => $users,
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === Method::POST) {
            $body = $request->getParsedBody();
            if (is_array($body) && isset($body['id'], $body['username'], $body['email'])) {
                $user = new User((string)$body['id'], (string)$body['username'], (string)$body['email']);
                $this->userRepository->save($user);

                $this->assignRoles($user->getId(), $body['roles'] ?? []);

                return $this->redirect('admin/user/index');
            }
        }

        return $this->responseFactory->createResponse([
            'roles' => $this->itemsStorage->getRoles(),
            'assignedRoles' => [],
        ]);
    }

    public function update(CurrentRoute $currentRoute, ServerRequestInterface $request): ResponseInterface
    {
        $id = $currentRoute->getArgument('id');
        if ($id === null) {
            return $this->redirect('admin/user/index');
        }

        $user = $this->userRepository->findById($id);
        if ($user === null) {
            return $this->redirect('admin/user/index');
        }

        if ($request->getMethod() === Method::POST) {
            $body = $request->getParsedBody();
            if (is_array($body) && isset($body['username'], $body['email'])) {
                $user->username = (string)$body['username'];
                $user->email = (string)$body['email'];
                $this->userRepository->save($user);

                $this->assignRoles($id, $body['roles'] ?? []);

                return $this->redirect('admin/user/index');
            }
        }

        $assigned = $this->assignmentsStorage->getByUserId($id);
        $assignedRoles = array_keys($assigned);

        return $this->responseFactory->createResponse([
            'user' => $user,
            'roles' => $this->itemsStorage->getRoles(),
            'assignedRoles' => $assignedRoles,
        ]);
    }

    public function delete(CurrentRoute $currentRoute, ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === Method::POST) {
            $id = $currentRoute->getArgument('id');
            if ($id !== null) {
                $this->userRepository->delete($id);
                $this->manager->revokeAll($id);
            }
        }

        return $this->redirect('admin/user/index');
    }

    private function assignRoles(string $userId, mixed $roles): void
    {
        $this->manager->revokeAll($userId);
        if (is_array($roles)) {
            foreach ($roles as $roleName) {
                $role = $this->itemsStorage->getRole((string)$roleName);
                if ($role !== null) {
                    $this->manager->assign($roleName, $userId);
                }
            }
        }
    }

    private function redirect(string $route): ResponseInterface
    {
        $url = $this->urlGenerator->generate($route);

        return $this->responseFactory
            ->createResponse('', 302)
            ->withHeader('Location', $url);
    }
}
