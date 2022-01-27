<?php

use app\helpers\Url;
use app\widgets\Menu;
use app\modules\admin\components\AppStatus;
use app\modules\admin\controllers\AdminController;
use app\modules\admin\controllers\SettingsController;


/** @var $settingsManager \app\settings\SettingsManager */
/** @var $settingsModel \app\settings\SettingsModel */
/** @var $this \yii\web\View */
/** @var $title string */
/** @var $content string */

$title = Yii::t('app', 'Settings');
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => Url::current()];
$appStatus = $this->params['appStatus'] ?? AppStatus::GOOD;
if ($appStatus == AppStatus::GOOD) {
    $badge = Yii::t('app', 'good');
    $badgeClass = 'pull-right label label-success';
} elseif ($appStatus == AppStatus::WARNING) {
    $badge = Yii::t('app', 'has warnings');
    $badgeClass = 'pull-right label label-warning';
} else {
    $badge = Yii::t('app', 'has problems');
    $badgeClass = 'pull-right label label-danger';
}
?>
<div class="row">
    <div class="col-xs-12 col-sm-3">
        <div class="box box-widget">
            <div class="box-body no-padding">
                <?= Menu::widget([
                    'options' => ['class' => 'nav nav-stacked nav-vertical'],
                    'items' => [
                        ['label' => Yii::t('app', 'Main settings'), 'url' => ['settings/index']],
                        ['label' => Yii::t('app', 'Photo settings'), 'url' => ['settings/photo']],
                        ['label' => Yii::t('app', 'Payment settings'), 'url' => ['settings/payment']],
                        ['label' => Yii::t('app', 'Price settings'), 'url' => ['settings/prices']],
                        ['label' => Yii::t('app', 'Social auth'), 'url' => ['settings/social']],
                        ['label' => Yii::t('app', 'Sex/Gender settings'), 'url' => ['settings/genders']],
                        ['label' => Yii::t('app', 'Cached data'), 'url' => ['settings/cached-data']],
                        [
                            'label' => Yii::t('app', 'Admin area'),
                            'url' => ['settings/admin'],
                            'active' => $this->context instanceof AdminController ||
                                ($this->context instanceof SettingsController && $this->context->action->id == 'admin'),
                        ],
                        ['label' => Yii::t('app', 'License key'), 'url' => ['settings/license']],
                        [
                            'label' => Yii::t('app', 'App status'),
                            'url' => ['settings/app-status'],
                            'badge' => $badge,
                            'badgeClass' => $badgeClass,
                        ],
                    ]
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-9">
        <?= $content ?>
    </div>
</div>
