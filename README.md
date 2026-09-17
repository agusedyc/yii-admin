# Yii Admin (agusedyc/yii-admin)

RBAC admin panel and authorization helpers for **Yii3**, built on top of
[`yiisoft/rbac`](https://github.com/yiisoft/rbac) and
[`yiisoft/rbac-db`](https://github.com/yiisoft/rbac-db).

This is a **non-backward-compatible** port of the Yii2 extension
[`agusedyc/yii2-mimin`](https://github.com/agusedyc/yii2-mimin) (minify of `mdmsoft/yii2-admin`).

## Features

- **Automatic route permission generation** — application routes are read directly from the Yii3
  router (`RouteCollectionInterface`), so there is nothing to mirror manually.
- **Route & Permission manager** — list, enable/disable and rename (alias/group) route permissions.
- **Role manager** — CRUD roles and grant permissions with a simple checkbox UI (JSON toggle endpoint).
- **Group hierarchy** — one parent permission per route group with all route permissions as children,
  so granting a group grants everything under it.
- **Access helpers** — `AccessService::checkRoute()`, `MenuFilter`, `ActionColumnFilter`
  (port of `Mimin::checkRoute()`, `filterMenu()`, `filterActionColumn()`).
- **Access middleware** — PSR-15 replacement for the Yii2 mimin `AccessControl` filter.
- **Console command** `admin:sync`.

## Requirements

- PHP 8.1 – 8.5
- A `yiisoft/db` connection (PostgreSQL is the primary supported driver; MySQL and SQLite also work)
- A Yii3 application (e.g. [`yiisoft/app`](https://github.com/yiisoft/app))

## Installation

```shell
composer require agusedyc/yii-admin
```

### Database

Add the migration source paths to your `config/params.php` (alongside the ones from
[`yiisoft/rbac-db`](https://github.com/yiisoft/rbac-db)):

```php
'yiisoft/db-migration' => [
    'sourcePaths' => [
        dirname(__DIR__) . '/vendor/yiisoft/rbac-db/migrations/items',
        dirname(__DIR__) . '/vendor/yiisoft/rbac-db/migrations/assignments',
        dirname(__DIR__) . '/vendor/agusedyc/yii-admin/migrations',
    ],
],
```

Then run the migrations (via [`yiisoft/db-migration`](https://github.com/yiisoft/db-migration)):

```shell
composer require yiisoft/db-migration
./yii migrate
```

The RBAC tables (`yii_rbac_item`, `yii_rbac_item_child`, `yii_rbac_assignment`) come from
`yiisoft/rbac-db`. The package adds a single metadata table `{{%admin_route}}`.

### RBAC storage

By default the package binds the database storages for you. You still need a `ConnectionInterface`
definition in your app (see [`yiisoft/db`](https://github.com/yiisoft/db) docs).

If your application already configures `yiisoft/rbac`, keep its `ManagerInterface` definition;
the package only re-binds `ItemsStorageInterface` / `AssignmentsStorageInterface` when they are
not overridden by your configuration.

## Configuration

All options live under the `agusedyc/yii-admin` key of `config/params.php`:

```php
return [
    'agusedyc/yii-admin' => [
        'enabled' => true,
        'path' => '/admin',
        'namePrefix' => 'admin/',
        'allowRoutes' => [
            'admin/*',
            'admin/**',
            'login',
        ],
        'ignoreRouteNames' => [
            'admin/*',
            'admin/**',
            'debug/*',
            'error/*',
        ],
        'ignoreRoutePatterns' => [],
        'groupPermissions' => true,
        'purgeMissingPermissions' => false,
        'layout' => 'layout.php',
        'viewPath' => null,
        'loginUrl' => '/login',
        'userIdProvider' => \Agusedyc\Admin\Access\CurrentUserProvider::class,
    ],
];
```

| Option | Default | Description |
| --- | --- | --- |
| `enabled` | `true` | Whether to register the admin routes. |
| `path` / `namePrefix` | `/admin` / `admin/` | URL prefix and route name prefix of the admin panel. |
| `allowRoutes` | `admin/*` | Routes skipped by `AccessMiddleware` (wildcards allowed). |
| `ignoreRouteNames` / `ignoreRoutePatterns` | — | Routes excluded from synchronization. |
| `groupPermissions` | `true` | Create a parent permission per group and attach route permissions as children. |
| `purgeMissingPermissions` | `false` | Remove RBAC permissions for deleted routes (`false` keeps assignments safe). |
| `layout` / `viewPath` | — | Custom layout / views directory for the admin panel. |
| `loginUrl` | `/login` | Redirect target for guests (`null` returns 403). |
| `userIdProvider` | `CurrentUserProvider` | Service id used to resolve the current user ID. |

## Usage

### 1. Synchronize routes

```shell
./yii admin:sync
```

Or press **Sync routes** in the admin panel. This:
- reads all named routes from the router,
- creates missing RBAC permissions (`yii_rbac_item`),
- stores display metadata and enabled/disabled state in `admin_route`,
- disables metadata for removed routes (permissions are kept unless `purgeMissingPermissions`),
- optionally creates per-group parent permissions and parent-child relations.

### 2. Admin panel

Open `/admin`:

- **Routes** — review every route permission, toggle it on/off (disabled routes are denied even if
  the RBAC permission is assigned), rename its alias and group.
- **Roles** — create/edit/delete roles and flip permissions on one by one. The screen groups
  permissions so you can grant an entire group at once.

### 3. Check access in code

Inject `AccessService` and use `checkRoute()`:

```php
if ($accessService->checkRoute('post/index')) {
    // user can access the route
}
```

`checkRoute(string $route, bool $strict = false)`. Non-strict mode also consults the
`allowRoutes` list and falls back to the parent *group* permission.

### 4. Filter menus

```php
$menuItems = $menuFilter->filter([
    ['label' => 'Home', 'url' => 'home'],
    ['label' => 'Posts', 'items' => [
        ['label' => 'List', 'url' => ['post/index']],
        ['label' => 'Create', 'url' => ['post/create']],
    ]],
]);
```

### 5. Middleware

Add `AccessMiddleware` to your route groups or the main middleware stack. It checks the current
route name/pattern against the RBAC permissions and the `allowRoutes` list:

```php
use Agusedyc\Admin\Middleware\AccessMiddleware;
use Yiisoft\Router\Group;

Group::create('/blog')
    ->middleware(AccessMiddleware::class)
    ->routes(/* ... */);
```

## Behavioral differences from yii2-mimin

| Yii2 (yii2-mimin) | Yii3 (this package) |
| --- | --- |
| `yii\rbac\DbManager` + `Yii::$app->authManager` | `Yiisoft\Rbac\ManagerInterface` |
| `Yii::$app->user->can()` | `AccessCheckerInterface::userHasPermission()` |
| `auth_item.type` int `1`/`2` | `yii_rbac_item.type` string `'role'`/`'permission'` |
| Auth items browsed from DB tables | Managed through the RBAC `Manager` API (`addRole`, `addChild`, …) |
| Route scan via controller reflection | Routes read from `RouteCollectionInterface` |
| Wildcard `/controller/*` permissions | Native RBAC hierarchy (group permissions) |
| `AccessControl` action filter + `Yii::$app->allowActions` | PSR-15 `AccessMiddleware` + `allowRoutes` config |
| `auth_item.data` column | Dropped (rule parameters are passed at check time) |
| ActiveRecord + widget (kartik) views | `yiisoft/view` + Bootstrap 5 views |

See [docs/upgrade-guide.md](docs/upgrade-guide.md) for a detailed migration guide including SQL.

## Development

```shell
docker compose -f docker/compose.yml build php
docker compose -f docker/compose.yml run --rm php composer install
docker compose -f docker/compose.yml run --rm php composer test
docker compose -f docker/compose.yml run --rm php composer psalm
docker compose -f docker/compose.yml run --rm php composer cs-fix
```

## License

[MIT](LICENSE.md). This project is not affiliated with or endorsed by the Yii Software Foundation.