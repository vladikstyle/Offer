<?php

use app\modules\admin\components\Permission;
use app\helpers\Html;
use app\modules\admin\models\Report;
use app\modules\admin\models\Verification;
use rmrevin\yii\fontawesome\FA;

/* @var $this \app\base\View */
/* @var $counters array */
/* @var $info array */
/* @var $photoModerationEnabled bool */

$this->title = Yii::t('app', 'Dashboard');
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['index']];
?>
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Users') ?></span>
                <span class="info-box-number"><?= $counters['users'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Online') ?></span>
                <span class="info-box-number"><?= $counters['usersOnline'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-photo"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Photos') ?></span>
                <span class="info-box-number"><?= $counters['photos'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Groups') ?></span>
                <span class="info-box-number"><?= $counters['groups'] ?></span>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header with-border">
        <h1 class="box-title">
            <?= Yii::t('app', 'Hello, {0}', Html::encode($this->getCurrentUser()->username)) ?>
        </h1>
    </div>
    <div class="box-body">
        <div class="moderator-todo-list container-fluid">

            <?php $tasks = false; ?>

            <?php if ($photoModerationEnabled && $this->getCurrentUser()->hasPermission(Permission::PHOTOS)): ?>
                <?php $tasks = true; ?>
                <div class="row moderator-todo-item <?= $counters['photosUnverified'] > 0 ? 'active' : '' ?>">
                    <div class="moderator-todo-title col-xs-12 col-sm-6 col-md-8 col-lg-9">
                        <?= FA::i('photo', ['class' => 'mr-1']) ?>
                        <?= Yii::t('app', 'Photos to verify') ?>
                    </div>
                    <div class="moderator-todo-action col-xs-12 col-sm-6 col-md-4 col-lg-3">
                        <?php if ($counters['photosUnverified'] > 0): ?>
                            <?= Html::a(Yii::t('app', 'Verify photos {0}', Html::tag('span', $counters['photosUnverified'], ['class' => 'label'])),
                                ['photo/index', 'unverified' => 1],
                                ['class' => 'btn btn-warning']) ?>
                        <?php else: ?>
                            <?= Html::a(Yii::t('app', 'All photos are verified'),
                                ['photo/index', 'unverified' => 1],
                                ['class' => 'btn btn-success']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($this->getCurrentUser()->hasPermission(Permission::REPORTS)): ?>
                <?php $tasks = true; ?>
                <div class="row moderator-todo-item <?= $counters['reportsNew'] > 0 ? 'active' : '' ?>">
                    <div class="moderator-todo-title col-xs-12 col-sm-6 col-md-8 col-lg-9">
                        <?= FA::i('users', ['class' => 'mr-1']) ?>
                        <?= Yii::t('app', 'Reported users') ?>
                    </div>
                    <div class="moderator-todo-action col-xs-12 col-sm-6 col-md-4 col-lg-3">
                        <?php if ($counters['reportsNew'] > 0): ?>
                            <?= Html::a(Yii::t('app', 'View reports {0}', Html::tag('span', $counters['reportsNew'], ['class' => 'ml-1 label'])),
                                ['report/index', 'type' => Report::TYPE_NEW],
                                ['class' => 'btn btn-warning']) ?>
                        <?php else: ?>
                            <?= Html::a(Yii::t('app', 'All reports are reviewed'),
                                ['report/index', 'type' => Report::TYPE_NEW],
                                ['class' => 'btn btn-success']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($this->getCurrentUser()->hasPermission(Permission::VERIFICATIONS)): ?>
                <?php $tasks = true; ?>
                <div class="row moderator-todo-item <?= $counters['verificationsNew'] > 0 ? 'active' : '' ?>">
                    <div class="moderator-todo-title col-xs-12 col-sm-6 col-md-8 col-lg-9">
                        <?= FA::i('check', ['class' => 'mr-1']) ?>
                        <?= Yii::t('app', 'Verification requests') ?>
                    </div>
                    <div class="moderator-todo-action col-xs-12 col-sm-6 col-md-4 col-lg-3">
                        <?php if ($counters['verificationsNew'] > 0): ?>
                            <?= Html::a(Yii::t('app', 'View requests {0}', Html::tag('span', $counters['verificationsNew'], ['class' => 'ml-1 label'])),
                                ['verification/index', 'type' => Verification::TYPE_NEW],
                                ['class' => 'btn btn-warning']) ?>
                        <?php else: ?>
                            <?= Html::a(Yii::t('app', 'All requests are reviewed'),
                                ['verification/index', 'type' => Verification::TYPE_NEW],
                                ['class' => 'btn btn-success']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$tasks): ?>
                <div class="row moderator-todo-item">
                    <div class="moderator-todo-title col-xs-12 col-sm-6 col-md-8 col-lg-9">
                        <?= FA::i('check', ['class' => 'mr-1']) ?>
                        <?= Yii::t('app', 'No tasks for you yet') ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
