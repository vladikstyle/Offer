<?php

/** @var string $content */
/** @var \app\base\View $this */
/** @var $user \app\models\User */

$user = Yii::$app->user->identity;
$profile = $user->profile;
?>
<?php $this->beginContent('@app/views/layouts/page-main.php'); ?>
<div class="page-content">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <?= \youdate\widgets\Sidebar::widget([
                'header' => Yii::t('youdate', 'Settings'),
                'items' => [
                    [
                        'label' => Yii::t('youdate', 'Profile'),
                        'url' => ['/settings/profile'],
                        'icon' => 'user',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Photos'),
                        'url' => ['/settings/photos'],
                        'icon' => 'image',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Access requests'),
                        'url' => ['/settings/access-requests'],
                        'icon' => 'eye',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Notifications'),
                        'url' => ['/settings/notifications'],
                        'icon' => 'bell',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Verification'),
                        'url' => ['/settings/verification'],
                        'icon' => 'check',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Appearance'),
                        'url' => ['/appearance/index'],
                        'icon' => 'sun',
                        'active' => $this->context instanceof \youdate\controllers\AppearanceController,
                        'count' => 'new',
                        'visible' => $this->themeSetting('darkModeUserOverride', true),
                    ],
                    [
                        'label' => Yii::t('youdate', 'Account'),
                        'url' => ['/settings/account'],
                        'icon' => 'settings',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Networks'),
                        'url' => ['/settings/networks'],
                        'icon' => 'link',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Blocked users'),
                        'url' => ['/settings/blocked-users'],
                        'icon' => 'users',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Your data'),
                        'url' => ['/settings/data'],
                        'icon' => 'database',
                    ],
                ],
            ]) ?>
        </div>
        <div class="col-lg-9">
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>
