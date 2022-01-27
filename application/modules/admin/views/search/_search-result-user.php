<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $model \app\models\User */
?>
<div class="search-result-item">
    <a href="<?= Url::to(['user/info', 'id' => $model->id]) ?>" data-pjax="0" class="search-image">
        <div class="img-wrapper">
            <img src="<?= $model->profile->getAvatarUrl(192, 192) ?>" alt="<?= Html::encode($model->profile->getDisplayName()) ?>">
        </div>
    </a>
    <a class="search-title" href="<?= Url::to(['user/info', 'id' => $model->id]) ?>" data-pjax="0">
        <?= Html::encode($model->profile->getDisplayName()) ?>
    </a>
    <span class="search-subtitle">
        <?= Html::encode($model->username) ?>
    </span>
</div>
