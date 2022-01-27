<?php

use app\modules\admin\components\Permission;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use rmrevin\yii\fontawesome\FA;

/** @var $this \app\base\View */
/** @var $user \app\models\User */
/** @var $content string */

$this->title = Yii::t('app', 'User') . ' #' . $user->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-5 col-md-4">
        <div class="box box-widget widget-user-2">
            <div class="widget-user-header bg-purple">
                <div class="widget-user-image">
                    <img class="img-circle" src="<?= $user->profile->getAvatarUrl(130, 130) ?>"
                         alt="<?= Html::encode($user->username) ?>">
                </div>
                <h3 class="widget-user-username"><?= Html::encode($user->username) ?></h3>
                <h5 class="widget-user-desc"><?= Html::encode($user->email) ?></h5>
            </div>
            <div class="box-footer no-padding">
                <?= Nav::widget([
                    'encodeLabels' => false,
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                    'items' => [
                        [
                            'label' => FA::icon('info-circle') . Yii::t('app', 'Information'),
                            'url' => ['user/info', 'id' => $user->id],
                        ],
                        [
                            'label' => FA::icon('pencil-square') . Yii::t('app', 'Edit account'),
                            'url' => ['user/update', 'id' => $user->id],
                            'visible' => $this->getCurrentUser()->isModerator && $user->isAdmin ? false : true,
                        ],
                        [
                            'label' => FA::icon('pencil-square') . Yii::t('app', 'Edit profile'),
                            'url' => ['user/update-profile', 'id' => $user->id],
                            'visible' => $this->getCurrentUser()->isModerator && $user->isAdmin ? false : true,
                        ],
                        [
                            'label' => FA::icon('money') . Yii::t('app', 'Balance'),
                            'url' => ['user/update-balance', 'id' => $user->id]
                        ],
                        [
                            'label' => FA::icon('photo') . Yii::t('app', 'Photos'),
                            'url' => ['photo/index', 'userId' => $user->id, 'unverified' => 0],
                            'visible' => $this->getCurrentUser()->hasPermission(Permission::PHOTOS),
                        ],
                        [
                            'label' => FA::icon('eye') . Yii::t('app', 'View on website'),
                            'url' => ['/profile/view', 'username' => $user->username]
                        ],
                    ],
                ]) ?>
            </div>
        </div>
        <div class="box box-widget">
            <div class="box-body no-padding">
                <?= Nav::widget([
                    'encodeLabels' => false,
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                    'items' => [
                        [
                            'label' => FA::icon('check-circle') . Yii::t('app', 'Confirm'),
                            'url' => ['user/confirm', 'id' => $user->id],
                            'visible' => !$user->isConfirmed,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to confirm this user?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('times-circle') . Yii::t('app', 'Block'),
                            'url' => ['user/block', 'id' => $user->id],
                            'visible' => !$user->isBlocked && !$user->isAdmin,
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to block this user?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('user') . Yii::t('app', 'Unblock'),
                            'url' => ['user/block', 'id' => $user->id],
                            'visible' => $user->isBlocked && !$user->isAdmin,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to unblock this user?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('user-plus') . Yii::t('app', 'Add to admins'),
                            'url' => ['user/toggle-admin', 'id' => $user->id],
                            'visible' => !$user->isAdmin && $this->getCurrentUser()->isAdmin,
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to add admin rights to this user?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('user-times') . Yii::t('app', 'Remove from admins'),
                            'url' => ['user/toggle-admin', 'id' => $user->id],
                            'visible' => $user->isAdmin && $this->getCurrentUser()->isAdmin,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to remove admin rights from this user?'),
                            ],
                        ],

                        [
                            'label' => FA::icon('check-square') . Yii::t('app', 'Add verification badge'),
                            'url' => ['user/toggle-verification', 'id' => $user->id],
                            'visible' => !$user->profile->is_verified,
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to add verification badge to this user?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('times') . Yii::t('app', 'Remove verification badge'),
                            'url' => ['user/toggle-verification', 'id' => $user->id],
                            'visible' => $user->profile->is_verified,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to remove verification badge from this user?'),
                            ],
                        ],

                        [
                            'label' => FA::icon('trash') . Yii::t('app', 'Delete'),
                            'url' => ['user/delete', 'id' => $user->id],
                            'visible' => $this->getCurrentUser()->isAdmin,
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to delete this user?'),
                            ],
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-sm-7 col-md-8">
        <?= $content ?>
    </div>
</div>
