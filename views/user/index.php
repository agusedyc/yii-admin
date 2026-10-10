<?php
/**
 * @var \Yiisoft\View\WebView $this
 * @var \Agusedyc\Admin\Model\User[] $users
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

use Yiisoft\Html\Html;

$this->setTitle('Users');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Users</h1>
    <a href="<?= Html::encode($urlGenerator->generate('admin/user/create')) ?>" class="btn btn-primary">Create User</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= Html::encode($user->getId()) ?></td>
                <td><?= Html::encode($user->username) ?></td>
                <td><?= Html::encode($user->email) ?></td>
                <td>
                    <a href="<?= Html::encode($urlGenerator->generate('admin/user/update', ['id' => $user->getId()])) ?>" class="btn btn-sm btn-info">Edit</a>
                    <form action="<?= Html::encode($urlGenerator->generate('admin/user/delete', ['id' => $user->getId()])) ?>" method="post" style="display:inline;" hx-post="<?= Html::encode($urlGenerator->generate('admin/user/delete', ['id' => $user->getId()])) ?>" hx-confirm="Are you sure?" hx-target="body">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
