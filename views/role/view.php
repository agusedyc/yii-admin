<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Rbac\Role $role
 * @var string $roleName
 * @var array<string, array<string, array{name: string, alias: string, checked: bool}>> $groups
 * @var string $csrfToken
 * @var string $csrfParam
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 */

use Yiisoft\Html\Html;

$endpoint = $url->generate('admin/role/permission', ['name' => $roleName]);
?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h1 class="h3 mb-0">Role <code><?= Html::encode($roleName) ?></code></h1>
    <div>
        <a class="btn btn-outline-primary" href="<?= Html::encode($url->generate('admin/role/edit', ['name' => $roleName])) ?>">Edit</a>
        <a class="btn btn-secondary" href="<?= Html::encode($url->generate('admin/role/index')) ?>">Kembali</a>
    </div>
</div>

<?php if ($role->getDescription() !== ''): ?>
    <p class="text-muted"><?= Html::encode($role->getDescription()) ?></p>
<?php endif; ?>

<div id="perm-status" class="alert d-none" role="alert"></div>

<?php if ($groups === []): ?>
    <div class="alert alert-info">Belum ada permission. Jalankan <code>admin:sync</code> terlebih dahulu.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($groups as $group => $permissions): ?>
            <div class="col-md-6">
                <div class="card perm-group">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><strong><?= Html::encode($group) ?></strong></span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input perm-group-toggle" type="checkbox"
                                   data-group="<?= Html::encode($group) ?>">
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($permissions as $permission): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <label class="mb-0">
                                    <code><?= Html::encode($permission['name']) ?></code>
                                    <?php if ($permission['alias'] !== $permission['name']): ?>
                                        <small class="text-muted">(<?= Html::encode($permission['alias']) ?>)</small>
                                    <?php endif; ?>
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input perm-toggle" type="checkbox"
                                           value="<?= Html::encode($permission['name']) ?>"
                                           data-group="<?= Html::encode($group) ?>"
                                           <?= $permission['checked'] ? 'checked' : '' ?>>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
(function () {
    const endpoint = <?= json_encode($endpoint) ?>;
    const csrfParam = <?= json_encode($csrfParam) ?>;
    const csrfToken = <?= json_encode($csrfToken) ?>;
    const status = document.getElementById('perm-status');

    function showStatus(ok, message) {
        status.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
        status.textContent = message;
        status.classList.remove('d-none');
        setTimeout(() => status.classList.add('d-none'), 2000);
    }

    async function toggle(checkbox) {
        const body = new FormData();
        body.append('permission', checkbox.value);
        body.append(csrfParam, csrfToken);

        checkbox.disabled = true;
        try {
            const response = await fetch(endpoint, {method: 'POST', body, headers: {'X-Requested-With': 'XMLHttpRequest'}});
            const data = await response.json();
            if (data.ok) {
                checkbox.checked = data.granted;
                showStatus(true, data.message);
            } else {
                checkbox.checked = !checkbox.checked;
                showStatus(false, data.message || 'Gagal menyimpan.');
            }
        } catch (error) {
            checkbox.checked = !checkbox.checked;
            showStatus(false, 'Terjadi kesalahan jaringan.');
        } finally {
            checkbox.disabled = false;
        }
    }

    document.querySelectorAll('.perm-toggle').forEach((checkbox) => {
        checkbox.addEventListener('change', () => toggle(checkbox));
    });

    document.querySelectorAll('.perm-group-toggle').forEach((groupToggle) => {
        const group = groupToggle.dataset.group;
        const children = Array.from(document.querySelectorAll('.perm-toggle[data-group="' + CSS.escape(group) + '"]'));
        const sync = () => {
            const checked = children.filter((c) => c.checked).length;
            groupToggle.checked = checked === children.length && children.length > 0;
            groupToggle.indeterminate = checked > 0 && checked < children.length;
        };
        sync();
        children.forEach((child) => child.addEventListener('change', sync));
        groupToggle.addEventListener('change', async () => {
            for (const child of children) {
                if (child.checked !== groupToggle.checked && !child.disabled) {
                    await toggle(child);
                }
            }
        });
    });
})();
</script>
