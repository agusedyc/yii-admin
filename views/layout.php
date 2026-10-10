<?php

declare(strict_types=1);

/**
 * @var string $content
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Session\Flash\FlashInterface $flash
 */

use Yiisoft\Html\Html;

$flashes = $flash->getAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RBAC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@1.9.11"></script>
    <style>
        body { padding-bottom: 3rem; }
        .perm-group { margin-bottom: 1.25rem; }
        .perm-group h6 { text-transform: capitalize; }
        .table-actions form { display: inline; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= Html::encode($url->generate('admin/route/index')) ?>">RBAC Admin</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="<?= Html::encode($url->generate('admin/route/index')) ?>">Routes</a>
            <a class="nav-link" href="<?= Html::encode($url->generate('admin/role/index')) ?>">Roles</a>
            <a class="nav-link" href="<?= Html::encode($url->generate('admin/user/index')) ?>">Users</a>
        </div>
        
        <?php
        // Fetch current active role and user roles inline for the layout
        /** @var \Yiisoft\Di\Container $container */
        // Wait, Yii3 view doesn't have container by default. We can use global or pass from controllers.
        // It's safer to just inject in controllers, but to avoid modifying all controllers, we might need a view parameter.
        // Wait, Yii3 AdminViewRenderer passes parameters.
        ?>
        <div class="navbar-nav">
            <form action="<?= Html::encode($url->generate('admin/role/switch')) ?>" method="post" hx-post="<?= Html::encode($url->generate('admin/role/switch')) ?>" hx-target="body">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Roles --</option>
                    <?php if (isset($appRoles, $activeAdminRole)): ?>
                        <?php foreach ($appRoles as $roleName): ?>
                            <option value="<?= Html::encode($roleName) ?>" <?= $activeAdminRole === $roleName ? 'selected' : '' ?>>
                                <?= Html::encode($roleName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    </div>
</nav>
<div class="container">
    <?php foreach ($flashes as $type => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode((string) $type) ?> alert-dismissible fade show" role="alert">
                <?= Html::encode((string) $message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?= $content ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
