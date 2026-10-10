<?php
/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Rbac\Role[] $roles
 * @var string[] $assignedRoles
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Create User');
?>

<h1>Create User</h1>

<?= $this->render('_form', [
    'user' => null,
    'roles' => $roles,
    'assignedRoles' => $assignedRoles,
    'actionUrl' => $urlGenerator->generate('admin/user/create'),
    'cancelUrl' => $urlGenerator->generate('admin/user/index'),
]) ?>
