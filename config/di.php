<?php

declare(strict_types=1);

/**
 * Package dependency injection configuration.
 *
 * Only package-owned services are bound here. Framework services (router, view, session,
 * CSRF, PSR-17 factories, user) are expected to be provided by the application.
 *
 * @var array $params
 */

use Agusedyc\Admin\Access\AccessService;
use Agusedyc\Admin\Access\ActionColumnFilter;
use Agusedyc\Admin\Access\CurrentUserProvider;
use Agusedyc\Admin\Access\MenuFilter;
use Agusedyc\Admin\Access\UserIdProviderInterface;
use Agusedyc\Admin\Middleware\AccessMiddleware;
use Agusedyc\Admin\Repository\DbRouteRepository;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;
use Agusedyc\Admin\Route\RouteProviderInterface;
use Agusedyc\Admin\Route\RouterRouteProvider;
use Agusedyc\Admin\Sync\RouteSynchronizer;
use Agusedyc\Admin\View\AdminViewRenderer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\View\WebView;

/** @var array<string, mixed> $options */
$options = $params['agusedyc/yii-admin'] ?? [];

return [
    // Default (database) RBAC storages. Applications may override these
    // definitions, e.g. to use a different storage backend.
    ItemsStorageInterface::class => ItemsStorage::class,
    AssignmentsStorageInterface::class => AssignmentsStorage::class,

    RouteRepositoryInterface::class => DbRouteRepository::class,

    RouteProviderInterface::class => static fn (RouteCollectionInterface $routeCollection): RouteProviderInterface
        => new RouterRouteProvider(
            $routeCollection,
            $options['ignoreRouteNames'] ?? [],
            $options['ignoreRoutePatterns'] ?? [],
        ),

    RouteSynchronizer::class => static fn (
        ManagerInterface $manager,
        RouteProviderInterface $routeProvider,
        RouteRepositoryInterface $routeRepository,
    ): RouteSynchronizer => new RouteSynchronizer(
        $manager,
        $routeProvider,
        $routeRepository,
        (bool) ($options['groupPermissions'] ?? true),
        (bool) ($options['purgeMissingPermissions'] ?? false),
    ),

    UserIdProviderInterface::class => static fn (ContainerInterface $container): UserIdProviderInterface
        => $container->get($options['userIdProvider'] ?? CurrentUserProvider::class),

    AccessService::class => static fn (
        ManagerInterface $manager,
        UserIdProviderInterface $userIdProvider,
        RouteRepositoryInterface $routeRepository,
    ): AccessService => new AccessService(
        $manager,
        $userIdProvider,
        $routeRepository,
        $options['allowRoutes'] ?? [],
    ),

    MenuFilter::class => static fn (AccessService $accessService): MenuFilter
        => new MenuFilter($accessService),

    ActionColumnFilter::class => static fn (AccessService $accessService): ActionColumnFilter
        => new ActionColumnFilter($accessService),

    AccessMiddleware::class => static fn (
        AccessService $accessService,
        CurrentRoute $currentRoute,
        ResponseFactoryInterface $responseFactory,
    ): AccessMiddleware => new AccessMiddleware(
        $accessService,
        $currentRoute,
        $responseFactory,
        $options['loginUrl'] ?? null,
    ),

    AdminViewRenderer::class => static fn (
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        Aliases $aliases,
        WebView $webView,
        CsrfTokenInterface $csrfToken,
        FlashInterface $flash,
        UrlGeneratorInterface $urlGenerator,
        CurrentRoute $currentRoute,
    ): AdminViewRenderer => new AdminViewRenderer(
        $responseFactory,
        $streamFactory,
        $aliases,
        $webView,
        $csrfToken,
        $flash,
        $urlGenerator,
        $currentRoute,
        $options,
    ),
];
