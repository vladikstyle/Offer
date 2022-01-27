<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use rmrevin\yii\fontawesome\FA;

/** @var $this \yii\web\View */
/** @var $group \app\models\Group */
/** @var $content string */

$this->title = Yii::t('app', 'Group') . ' #' . $group->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-5 col-md-4">
        <div class="box box-widget widget-user-2">
            <div class="widget-user-header bg-purple">
                <?php if (isset($group->photo_path)): ?>
                    <div class="widget-user-image">
                        <img class="img-circle" src="<?= $group->getPhotoThumbnail(130, 130) ?>"
                             alt="<?= Html::encode($group->title) ?>">
                    </div>
                <?php endif; ?>
                <h3 class="widget-user-username"><?= Html::encode($group->title) ?></h3>
                <h5 class="widget-user-desc"><?= Html::encode($group->alias) ?></h5>
            </div>
            <div class="box-footer no-padding">
                <?= Nav::widget([
                    'encodeLabels' => false,
                    'options' => [
                        'class' => 'nav nav-pills nav-stacked',
                    ],
                    'items' => [
                        [
                            'label' => FA::icon('pencil') . Yii::t('app', 'Update'),
                            'url' => ['group/update', 'id' => $group->id]
                        ],
                        [
                            'label' => FA::icon('eye') . Yii::t('app', 'View on website'),
                            'url' => ['/group/view', 'alias' => $group->alias]
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
                        'class' => 'nav nav-pills nav-stacked',
                    ],
                    'items' => [
                        [
                            'label' => FA::icon('check-square') . Yii::t('app', 'Add verification badge'),
                            'url' => ['group/toggle-verification', 'id' => $group->id],
                            'visible' => !$group->is_verified,
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to add verification badge to this group?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('times') . Yii::t('app', 'Remove verification badge'),
                            'url' => ['group/toggle-verification', 'id' => $group->id],
                            'visible' => $group->is_verified,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to remove verification badge from this group?'),
                            ],
                        ],
                        [
                            'label' => FA::icon('trash') .Yii::t('app', 'Delete'),
                            'url' => ['group/delete', 'id' => $group->id],
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to delete this group?'),
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
