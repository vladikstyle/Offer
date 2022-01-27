<?php

use app\helpers\Html;
use app\helpers\Url;
use youdate\helpers\Icon;

/** @var bool $showBalance */
/** @var \app\base\View $this */

$user = $this->getCurrentUser();
$profile = $this->getCurrentUserProfile();
?>
<?php if ($showBalance): ?>
    <div class="nav-item d-none d-sm-block">
        <a href="<?= Url::to(['balance/services']) ?>"
           class="btn btn-outline-primary btn-sm"
           data-pjax="0"
           title="<?= Yii::t('youdate', 'Balance') ?>" rel="tooltip">
            <?= Icon::fa('money', ['class' => 'mr-2']) ?>
            <span class="user-balance"><?= $this->params['user.balance'] ?></span>
        </a>
    </div>
<?php endif; ?>
<div class="dropdown">
    <a href="<?= Url::to(['/profile/index']) ?>" class="nav-link pr-0 leading-none" data-toggle="dropdown">
        <span class="avatar" style="background-image: url(<?= $profile->getAvatarUrl(64, 64) ?>)"></span>
        <span class="ml-2 d-none d-lg-block">
            <span class="text-default"><?= Html::encode($profile->getDisplayName()) ?></span>
            <small class="text-muted d-block mt-1"><?= Html::encode($user->username) ?></small>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
        <a class="dropdown-item" href="<?= Url::to(['/profile/index']) ?>">
            <?= Icon::fe('user', ['class' => 'dropdown-icon mr-2']) ?>
            <?= Yii::t('youdate', 'Profile') ?>
        </a>
        <a class="dropdown-item" href="<?= Url::to(['/settings/profile']) ?>">
            <?= Icon::fe('settings', ['class' => 'dropdown-icon mr-2']) ?>
            <?= Yii::t('youdate', 'Settings') ?>
        </a>
        <a class="dropdown-item d-block d-md-none" href="<?= Url::to(['/notifications/index']) ?>">
            <?= Icon::fe('bell', ['class' => 'dropdown-icon mr-2']) ?>
            <?= Yii::t('youdate', 'Notifications') ?>
        </a>
        <?php if ($showBalance): ?>
            <a class="dropdown-item d-block d-sm-none" href="<?= Url::to(['/balance/services']) ?>">
                <span class="float-right"><span class="badge badge-primary"><?= $this->params['user.balance'] ?></span></span>
                <?= Icon::fe('dollar-sign', ['class' => 'dropdown-icon mr-2']) ?>
                <?= Yii::t('youdate', 'Balance') ?>
            </a>
        <?php endif; ?>
        <?php if ($user->isAdmin || $user->isModerator): ?>
            <a class="dropdown-item" href="<?= Url::to(['/' . env('ADMIN_PREFIX')]) ?>">
                <?= Icon::fe('sliders', ['class' => 'dropdown-icon mr-2']) ?>
                <?= Yii::t('youdate', 'Administration') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="<?= Url::to(['/help/index']) ?>">
            <?= Icon::fe('help-circle', ['class' => 'dropdown-icon mr-2']) ?>
            <?= Yii::t('youdate', 'Need help?') ?>
        </a>
        <a class="dropdown-item" href="<?= Url::to(['/news/index']) ?>">
            <?= Icon::fe('file-text', ['class' => 'dropdown-icon mr-2']) ?>
            <?= Yii::t('youdate', 'News') ?>
        </a>
        <a class="dropdown-item" data-method="post" href="<?= Url::to(['/security/logout']) ?>">
            <?= Icon::fe('log-out', ['class' => 'dropdown-icon mr-2']) ?>
            <?= Yii::t('youdate', 'Sign out') ?>
        </a>
    </div>
</div>
