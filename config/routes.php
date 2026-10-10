<?php

declare(strict_types=1);

/**
 * Admin panel routes. Registered only when the package is enabled in params.
 *
 * @var array $params
 */

use Agusedyc\Admin\Controller\Role;
use Agusedyc\Admin\Controller\Route as RouteController;
use Agusedyc\Admin\Controller\User\UserController;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

if (empty($params['agusedyc/yii-admin']['enabled'])) {
    return [];
}

$prefix = (string) ($params['agusedyc/yii-admin']['path'] ?? '/admin');
$namePrefix = (string) ($params['agusedyc/yii-admin']['namePrefix'] ?? 'admin/');

return [
    Group::create($prefix)
        ->namePrefix($namePrefix)
        ->routes(
            Group::create('/route')
                ->namePrefix('route/')
                ->routes(
                    Route::get('')->action(RouteController\IndexAction::class)->name('index'),
                    Route::post('/sync')->action(RouteController\SyncAction::class)->name('sync'),
                    Route::post('/{name}/toggle')->action(RouteController\ToggleAction::class)->name('toggle'),
                    Route::get('/{name}/edit')->action(RouteController\EditAction::class)->name('edit'),
                    Route::post('/{name}/edit')->action(RouteController\UpdateAction::class)->name('update'),
                ),
            Group::create('/role')
                ->namePrefix('role/')
                ->routes(
                    Route::get('')->action(Role\IndexAction::class)->name('index'),
                    Route::get('/create')->action(Role\CreateAction::class)->name('create'),
                    Route::post('/create')->action(Role\CreateAction::class)->name('create.post'),
                    Route::get('/{name}')->action(Role\ViewAction::class)->name('view'),
                    Route::get('/{name}/edit')->action(Role\EditAction::class)->name('edit'),
                    Route::post('/{name}/edit')->action(Role\UpdateAction::class)->name('update'),
                    Route::post('/{name}/delete')->action(Role\DeleteAction::class)->name('delete'),
                    Route::post('/{name}/permission')->action(Role\PermissionAction::class)->name('permission'),
                ),
            Group::create('/user')
                ->namePrefix('user/')
                ->routes(
                    Route::get('')->action([UserController::class, 'index'])->name('index'),
                    Route::methods(['GET', 'POST'], '/create')->action([UserController::class, 'create'])->name('create'),
                    Route::methods(['GET', 'POST'], '/{id}/update')->action([UserController::class, 'update'])->name('update'),
                    Route::post('/{id}/delete')->action([UserController::class, 'delete'])->name('delete'),
                ),
        ),
];
