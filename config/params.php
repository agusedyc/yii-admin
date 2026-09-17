<?php

declare(strict_types=1);

use Agusedyc\Admin\Command\SyncCommand;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'admin:sync' => SyncCommand::class,
        ],
    ],
    'yiisoft/db-migration' => [
        'sourcePaths' => [
            __DIR__ . '/../migrations',
        ],
    ],
    'agusedyc/yii-admin' => [
        /**
         * Whether the admin panel routes must be registered.
         */
        'enabled' => true,

        /**
         * URL prefix for the admin panel routes.
         */
        'path' => '/admin',

        /**
         * Route name prefix for the admin panel routes.
         */
        'namePrefix' => 'admin/',

        /**
         * Routes that are skipped by {@see \Agusedyc\Admin\Middleware\AccessMiddleware}.
         * Supports `*` wildcards, matched against both route names and patterns.
         *
         * @psalm-var list<string>
         */
        'allowRoutes' => [
            'admin/*',
            'admin/**',
        ],

        /**
         * Route names ignored during route collection/synchronization.
         * Supports `*` wildcards.
         *
         * @psalm-var list<string>
         */
        'ignoreRouteNames' => [
            'admin/*',
            'admin/**',
            'debug/*',
            'error/*',
        ],

        /**
         * Route URL patterns ignored during route collection/synchronization.
         * Supports `*` wildcards.
         *
         * @psalm-var list<string>
         */
        'ignoreRoutePatterns' => [],

        /**
         * Create a parent permission per route group and attach every route permission
         * as its child. This allows granting a whole group by assigning a single permission.
         */
        'groupPermissions' => true,

        /**
         * Remove RBAC permissions for routes that no longer exist during synchronization.
         * Disabled by default to keep existing user assignments intact.
         */
        'purgeMissingPermissions' => false,

        /**
         * Layout file used to render admin views, relative to the package views directory.
         * Set to `null` to render views without a layout.
         */
        'layout' => 'layout.php',

        /**
         * Absolute path or alias to the directory that contains admin views.
         * `null` means the package's own `views` directory.
         */
        'viewPath' => null,

        /**
         * URL to redirect guests to when access is denied. When `null`, a 403 response is returned.
         */
        'loginUrl' => '/login',

        /**
         * Service id or class name of the user id provider.
         *
         * @psalm-var class-string
         */
        'userIdProvider' => \Agusedyc\Admin\Access\CurrentUserProvider::class,
    ],
];
