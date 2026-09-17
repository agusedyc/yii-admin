<?php

declare(strict_types=1);

/**
 * @var \Agusedyc\Admin\Repository\RouteRecord[] $records
 * @var string $csrfToken
 * @var string $csrfParam
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 */

use Yiisoft\Html\Html;

?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Routes</h1>
    <form method="post" action="<?= Html::encode($url->generate('admin/route/sync')) ?>">
        <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">
        <button type="submit" class="btn btn-primary">Sync routes</button>
    </form>
</div>

<?php if ($records === []): ?>
    <div class="alert alert-info">Belum ada route. Jalankan sync untuk mengimpor route aplikasi.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead>
            <tr>
                <th>Name</th>
                <th>Pattern</th>
                <th>Methods</th>
                <th>Group</th>
                <th>Alias</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><code><?= Html::encode($record->getName()) ?></code></td>
                    <td><code><?= Html::encode($record->getPattern()) ?></code></td>
                    <td><?= Html::encode($record->getMethods()) ?></td>
                    <td><?= Html::encode($record->getGroup()) ?></td>
                    <td><?= Html::encode($record->getAlias()) ?></td>
                    <td>
                        <?php if ($record->isActive()): ?>
                            <span class="badge bg-success">active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end table-actions">
                        <form method="post" action="<?= Html::encode($url->generate('admin/route/toggle', ['name' => $record->getName()])) ?>">
                            <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <?= $record->isActive() ? 'Disable' : 'Enable' ?>
                            </button>
                        </form>
                        <a class="btn btn-sm btn-outline-primary"
                           href="<?= Html::encode($url->generate('admin/route/edit', ['name' => $record->getName()])) ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
