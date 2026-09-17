<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Rbac\Role[] $roles
 * @var array<string, int> $permissionCounts
 * @var int $permissionCount
 * @var int $activeRouteCount
 * @var string $csrfToken
 * @var string $csrfParam
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 */

use Yiisoft\Html\Html;

?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Roles</h1>
    <a class="btn btn-primary" href="<?= Html::encode($url->generate('admin/role/create')) ?>">Create role</a>
</div>

<p class="text-muted">
    <?= count($roles) ?> role &middot;
    <?= $permissionCount ?> permission &middot;
    <?= $activeRouteCount ?> route aktif
</p>

<?php if ($roles === []): ?>
    <div class="alert alert-info">Belum ada role.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Permissions</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($roles as $name => $role): ?>
                <tr>
                    <td><a href="<?= Html::encode($url->generate('admin/role/view', ['name' => $name])) ?>">
                            <code><?= Html::encode($name) ?></code>
                        </a></td>
                    <td><?= Html::encode($role->getDescription()) ?></td>
                    <td><span class="badge bg-info text-dark"><?= $permissionCounts[$name] ?? 0 ?></span></td>
                    <td class="text-end table-actions">
                        <a class="btn btn-sm btn-outline-primary"
                           href="<?= Html::encode($url->generate('admin/role/edit', ['name' => $name])) ?>">Edit</a>
                        <form method="post" action="<?= Html::encode($url->generate('admin/role/delete', ['name' => $name])) ?>"
                              onsubmit="return confirm('Hapus role <?= Html::encode($name) ?>?');">
                            <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
