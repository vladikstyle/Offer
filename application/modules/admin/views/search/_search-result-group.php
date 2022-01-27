<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $model \app\models\Group */
?>
<div class="search-result-item">
    <a href="<?= Url::to(['group/update', 'id' => $model->id]) ?>" data-pjax="0" class="search-image">
        <div class="img-wrapper">
            <?php if (isset($model->photo_path)): ?>
                <img src="<?= $model->getPhotoThumbnail(192, 192) ?>" alt="<?= Html::encode($model->getDisplayTitle()) ?>">
            <?php else: ?>
                <div class="no-photo">
                    <i class="fa fa-image"></i>
                </div>
            <?php endif; ?>
        </div>
    </a>
    <a class="search-title" href="<?= Url::to(['user/info', 'id' => $model->id]) ?>" data-pjax="0">
        <?= Html::encode($model->getDisplayTitle()) ?>
    </a>
    <div class="search-subtitle">
        <?= Html::encode($model->alias) ?>
    </div>
</div>
