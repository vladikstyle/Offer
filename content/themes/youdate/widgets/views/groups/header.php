<?php

use app\helpers\Html;
use app\models\GroupUser;
use youdate\helpers\Icon;

/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */
/* @var $canView bool */
/* @var $canManage bool */
/* @var $showCover bool */
/* @var $showBackButton bool */
?>
<div class="group-header">
    <?php if ($showCover === true): ?>
        <div class="group-cover" <?= isset($group->cover_path) ? 'style="background-image: url(' . $group->getCoverUrl() . ')"' : '' ?>></div>
    <?php endif; ?>
    <div class="group-info">
        <div class="d-block d-md-flex align-items-center">
            <div class="info d-flex flex-grow-1">
                <div class="group-photo mr-3 flex-shrink-0">
                    <?php if (isset($group->photo_path)): ?>
                        <?= Html::img($group->getPhotoThumbnail(96, 96)) ?>
                    <?php else: ?>
                        <div class="no-photo"></div>
                    <?php endif; ?>
                </div>
                <div class="group-info-lines w-100">
                    <h1>
                        <?= $group->getDisplayTitle() ?>
                        <?php if ($group->is_verified): ?>
                            <span class="group-verified-badge" rel="tooltip"
                                  title="<?= Yii::t('youdate', 'Verified group') ?>">
                                <?= Icon::fe('check') ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                    <div class="description">
                        <?= Html::encode($group->getShortDescription()) ?>
                    </div>
                </div>
            </div>
            <div class="actions d-flex flex-column flex-md-row ml-0 ml-md-auto">
                <?php if ($showBackButton === false): ?>
                    <?php if ($groupUser == null): ?>
                        <?= Html::a(Yii::t('youdate', 'Join group'),
                            ['join', 'alias' => $group->alias],
                            [
                                'class' => 'btn btn-primary',
                                'data-method' => 'post',
                            ]
                        ) ?>
                    <?php elseif ($groupUser->status == GroupUser::STATUS_UNDER_MODERATION): ?>
                        <button class="btn btn-outline-secondary btn-disabled">
                            <?= Icon::fe('check', ['class' => 'mr-2']) ?>
                            <?= Yii::t('youdate', 'Access requested') ?>
                        </button>
                    <?php elseif ($groupUser->status == GroupUser::STATUS_BANNED): ?>
                        <button class="btn btn-outline-danger btn-disabled">
                            <?= Icon::fe('alert-circle', ['class' => 'mr-2']) ?>
                            <?= Yii::t('youdate', 'You\'re banned') ?>
                        </button>
                    <?php else: ?>
                        <div class="dropdown d-block d-md-inline">
                            <?= Html::button(Icon::fe('check', ['class' => 'mr-2']) . Yii::t('youdate', 'Following'),
                                [
                                    'class' => 'btn btn-outline-primary btn-block btn-md-inline mt-2 mt-md-0 dropdown-toggle',
                                    'data-toggle' => 'dropdown',
                                    'aria-haspopup' => true,
                                    'aria-expanded' => false,
                                ]
                            ) ?>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <?= Html::a(Yii::t('youdate', 'Leave group'),
                                    ['leave', 'alias' => $group->alias],
                                    [
                                        'class' => 'dropdown-item',
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('youdate', 'Are you sure you want to leave this group?'),
                                    ]
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($canManage): ?>
                        <?= Html::a(Icon::fe('settings', ['class' => 'mr-2']) . Yii::t('youdate', 'Management'),
                            ['management-update', 'alias' => $group->alias],
                            ['class' => 'btn btn-secondary mt-2 mt-md-0 ml-0 ml-md-2']
                        ) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?= Html::a('<span class="mr-2">&larr;</span>' . Yii::t('youdate', 'Back'),
                        ['view', 'alias' => $group->alias],
                        ['class' => 'btn btn-secondary btn-block btn-md-inline mt-2 mt-md-0']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
