<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Middleware;

use Agusedyc\Admin\Access\AccessService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * PSR-15 access control middleware: the Yii3 equivalent of the Yii2 mimin `AccessControl` filter.
 *
 * Skipped routes are defined in the package params (`allowRoutes`). Guests are redirected to
 * the configured login URL (when set) or rejected with 403; authenticated users without the
 * required permission get 403.
 */
final class AccessMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AccessService $access,
        private readonly CurrentRoute $currentRoute,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ?string $loginUrl = null,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeName = $this->currentRoute->getName();
        $routePattern = $this->currentRoute->getPattern();

        if ($routeName !== null && $this->access->isAllowed($routeName)) {
            return $handler->handle($request);
        }

        if ($routePattern !== null && $this->access->isAllowed($routePattern)) {
            return $handler->handle($request);
        }

        if ($routeName !== null && $this->access->checkRoute($routeName, strict: true)) {
            return $handler->handle($request);
        }

        if ($routeName === null && $routePattern !== null && $this->access->checkRoute($routePattern, strict: true)) {
            return $handler->handle($request);
        }

        if ($this->access->isGuest() && $this->loginUrl !== null) {
            return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', $this->loginUrl);
        }

        return $this->responseFactory->createResponse(403);
    }
}
