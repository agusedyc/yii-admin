<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Role;

use Agusedyc\Admin\Access\ActiveRoleAccessChecker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Session\SessionInterface;

final class SwitchRoleController
{
    public function __construct(
        private readonly DataResponseFactoryInterface $responseFactory,
        private readonly SessionInterface $session
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (is_array($body) && !empty($body['role'])) {
            $this->session->set(ActiveRoleAccessChecker::SESSION_KEY, (string)$body['role']);
        } else {
            $this->session->remove(ActiveRoleAccessChecker::SESSION_KEY);
        }

        // HTMX redirect to refresh the page
        return $this->responseFactory
            ->createResponse()
            ->withHeader('HX-Redirect', '/admin/role'); // or fallback to referrer if non-htmx
    }
}
