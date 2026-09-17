<?php

declare(strict_types=1);

/**
 * @var \Agusedyc\Admin\Repository\RouteRecord $record
 * @var string $csrfToken
 * @var string $csrfParam
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 */

use Yiisoft\Html\Html;

?>
<h1 class="h3 mb-3">Edit route</h1>

<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9"><code><?= Html::encode($record->getName()) ?></code></dd>
            <dt class="col-sm-3">Pattern</dt>
            <dd class="col-sm-9"><code><?= Html::encode($record->getPattern()) ?></code></dd>
            <dt class="col-sm-3">Methods</dt>
            <dd class="col-sm-9"><?= Html::encode($record->getMethods()) ?></dd>
        </dl>
    </div>
</div>

<form method="post" action="<?= Html::encode($url->generate('admin/route/update', ['name' => $record->getName()])) ?>">
    <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">

    <div class="mb-3">
        <label for="alias" class="form-label">Alias</label>
        <input type="text" class="form-control" id="alias" name="alias"
               value="<?= Html::encode($record->getAlias()) ?>" required>
    </div>

    <div class="mb-3">
        <label for="group" class="form-label">Group</label>
        <input type="text" class="form-control" id="group" name="group"
               value="<?= Html::encode($record->getGroup()) ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a class="btn btn-secondary" href="<?= Html::encode($url->generate('admin/route/index')) ?>">Batal</a>
</form>
