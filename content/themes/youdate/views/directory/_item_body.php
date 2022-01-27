<?php

use app\helpers\Url;
use app\helpers\Html;
use youdate\helpers\Icon;

/** @var $model \app\models\User */
/** @var $profile \app\models\Profile */

$profile = $model->profile;
$profileUrl = Url::to(['/profile/view', 'username' => $model->username]);
$subString = isset($subString) ? $subString : $profile->getDisplayLocation();
?>
<div class="card <?= $model->isPremium ? 'card-premium' : '' ?>" data-user-id="<?= $model->id ?>">
    <a href="<?= $profileUrl ?>" class="user-photo" data-pjax="0">
        <div class="card-img-top-wrapper d-flex justify-content-center">
            <div class="loader">
                <?= Icon::fa('spin') ?>
            </div>
            <img class="card-img-top"
                 src="<?= $profile->getAvatarUrl(500, 500) ?>" alt="<?= Html::encode($profile->getDisplayName()) ?>">
        </div>
        <?php if ($model->photosCount): ?>
            <div class="user-photos-count">
                <?= $model->photosCount ?><?= Icon::fe('image', ['class' => 'ml-1']) ?>
            </div>
        <?php endif; ?>
    </a>
    <div class="card-body d-flex flex-column" style="position: relative">
        <h4 class="d-flex justify-content-start align-items-center">
            <a href="<?= $profileUrl ?>" data-pjax="0">
                <span class="display-name"><?= Html::encode($profile->getDisplayName()) ?>, <?= $profile->getAge() ?></span>
            </a>
            <?php if ($model->profile->is_verified): ?>
                <?= $this->render('/partials/verified-badge', ['model' => $model]) ?>
            <?php endif; ?>
            <?php if ($model->isPremium): ?>
                <?= $this->render('/partials/premium-badge', ['model' => $model]) ?>
            <?php endif; ?>
            <div class="ml-auto">
                <?= $this->render('/partials/online-status', ['model' => $model]) ?>
            </div>
        </h4>
        <div class="text-muted"><?= $subString ?></div>
    </div>
</div>
