<?php

use app\helpers\Url;
use app\helpers\Html;
use youdate\helpers\Icon;

/** @var $model \app\models\Group */

$groupUrl = Url::to(['/group/view', 'alias' => $model->alias]);
$subString = isset($subString) ? $subString : $model->getDisplayLocation();
?>
<div class="card card-aside" data-group-id="<?= $model->id ?>">
    <?php if (isset($model->photo)): ?>
        <a href="<?= $groupUrl ?>"
           title="<?= Html::encode($model->getDisplayTitle()) ?>"
           class="card-aside-column" style="background-image: url(<?= $model->getPhotoThumbnail(400, 400) ?>)"></a>
    <?php else: ?>
        <a href="<?= $groupUrl ?>" class="card-aside-column no-photo d-flex justify-content-center align-items-center">
            <?= Icon::fe('image') ?>
        </a>
    <?php endif; ?>
    <?php if ($model->is_verified): ?>
        <div class="group-verified-badge" rel="tooltip"
             title="<?= Yii::t('youdate', 'Verified group') ?>">
            <?= Icon::fe('check') ?>
        </div>
    <?php endif; ?>
    <div class="card-body d-flex flex-column">
        <h4><a href="<?= $groupUrl ?>"><?= Html::encode($model->getDisplayTitle()) ?></a></h4>
        <div class="text-muted max-lines-3"><?= Html::encode($model->getShortDescription()) ?></div>
        <div class="d-flex align-items-center pt-5 mt-auto">
            <div class="badge badge-secondary">
                <?= $model->getGroupUsersCount() ?>
                <?= Icon::fe('users', ['class' => 'ml-1']) ?>
            </div>
        </div>
    </div>
</div>
