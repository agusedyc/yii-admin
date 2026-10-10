<?php
/**
 * @var \Yiisoft\View\WebView $this
 * @var \Agusedyc\Admin\Model\User $user
 * @var \Yiisoft\Rbac\Role[] $roles
 * @var string[] $assignedRoles
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

use Yiisoft\Html\Html;

$this->setTitle('Update User: ' . $user->username);
?>

<h1>Update User: <?= Html::encode($user->username) ?></h1>

<?= $this->render('_form', [
    'user' => $user,
    'roles' => $roles,
    'assignedRoles' => $assignedRoles,
    'actionUrl' => $urlGenerator->generate('admin/user/update', ['id' => $user->getId()]),
    'cancelUrl' => $urlGenerator->generate('admin/user/index'),
]) ?>
