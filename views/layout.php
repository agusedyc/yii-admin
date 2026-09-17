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
        <div class="navbar-nav">
            <a class="nav-link" href="<?= Html::encode($url->generate('admin/route/index')) ?>">Routes</a>
            <a class="nav-link" href="<?= Html::encode($url->generate('admin/role/index')) ?>">Roles</a>
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
