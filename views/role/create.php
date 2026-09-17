<?php

declare(strict_types=1);

/**
 * @var string $csrfToken
 * @var string $csrfParam
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 */

use Yiisoft\Html\Html;

?>
<h1 class="h3 mb-3">Create role</h1>

<form method="post" action="<?= Html::encode($url->generate('admin/role/create')) ?>">
    <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <input type="text" class="form-control" id="description" name="description">
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a class="btn btn-secondary" href="<?= Html::encode($url->generate('admin/role/index')) ?>">Batal</a>
</form>
