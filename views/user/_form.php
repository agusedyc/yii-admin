<?php
/**
 * @var \Yiisoft\View\WebView $this
 * @var \Agusedyc\Admin\Model\User|null $user
 * @var \Yiisoft\Rbac\Role[] $roles
 * @var string[] $assignedRoles
 * @var string $actionUrl
 * @var string $cancelUrl
 */

use Yiisoft\Html\Html;
?>

<form action="<?= Html::encode($actionUrl) ?>" method="post" hx-post="<?= Html::encode($actionUrl) ?>" hx-target="body">
    <?php if ($user === null): ?>
        <div class="mb-3">
            <label for="id" class="form-label">ID</label>
            <input type="text" class="form-control" id="id" name="id" required>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <label class="form-label">ID</label>
            <input type="text" class="form-control" value="<?= Html::encode($user->getId()) ?>" disabled>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?= $user !== null ? Html::encode($user->username) : '' ?>" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= $user !== null ? Html::encode($user->email) : '' ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Roles</label>
        <div>
            <?php foreach ($roles as $roleName => $role): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="roles[]" value="<?= Html::encode($roleName) ?>" id="role_<?= Html::encode($roleName) ?>" <?= in_array($roleName, $assignedRoles, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="role_<?= Html::encode($roleName) ?>">
                        <?= Html::encode($roleName) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-success">Save</button>
    <a href="<?= Html::encode($cancelUrl) ?>" class="btn btn-secondary">Cancel</a>
</form>
