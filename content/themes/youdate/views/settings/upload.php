<?php

use app\helpers\Url;
use app\helpers\Html;
use yii\helpers\ArrayHelper;
use youdate\helpers\Icon;
use youdate\widgets\ActiveForm;
use youdate\widgets\Upload;
use youdate\widgets\EmptyState;

/** @var $model \app\models\Profile */
/** @var $form \yii\widgets\ActiveForm */
/** @var $this \app\base\View */
/** @var $uploadForm \app\forms\UploadForm */
/** @var $settings array */
/** @var $currentPhotosCount int */

$this->title = Yii::t('youdate', 'Upload Photos');
$this->params['breadcrumbs'][] = $this->title;
$this->params['user.showUploadPhotoRequest'] = false;
$this->registerJsFile('@themeUrl/static/js/settings.js', [
    'depends' => [
        \youdate\assets\Asset::class,
        \youdate\assets\UploadAsset::class,
    ],
]);
$photoMaxFiles = ArrayHelper::getValue($settings, 'photoMaxFiles', 10);
$photoMaxPerProfile = ArrayHelper::getValue($settings, 'photoMaxPerProfile', 0);
$maxFiles = $photoMaxPerProfile;
if ($photoMaxPerProfile > 0) {
    $maxFiles = $photoMaxPerProfile - $currentPhotosCount;
}
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-options">
            <a href="<?= Url::to(['/settings/photos']) ?>" class="btn btn-secondary btn-sm">
                <?= Icon::fe('arrow-left') ?>
                <?= Yii::t('youdate', 'Back') ?>
            </a>
        </div>
    </div>
    <?php $form = ActiveForm::begin() ?>
    <div class="card-body">
        <?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

        <?= $form->errorSummary($uploadForm) ?>

        <?php if ($maxFiles > 0): ?>
            <?= $form->field($uploadForm, 'photos')->widget(Upload::class, [
                'id' => 'photo-upload',
                'url' => ['/photo/upload-photo'],
                'multiple' => true,
                'sortable' => false,
                'maxFileSize' => ArrayHelper::getValue($settings, 'photoMaxFileSize', 20) * 1024 * 1024,
                'maxNumberOfFiles' => $maxFiles,
            ])->label(false); ?>
        <?php else: ?>
            <?= EmptyState::widget([
                'icon' => 'fe fe-image',
                'title' => Yii::t('youdate', 'Limit exceeded'),
                'subTitle' => Yii::t('youdate', 'Sorry, but you have already uploaded the maximum amount of photos'),
            ]) ?>
        <?php endif; ?>
    </div>
    <div class="card-footer form-actions">
        <div class="d-flex justify-content-between align-items-center">
            <?php if ($maxFiles > 0 && $currentPhotosCount < $photoMaxPerProfile): ?>
                <span class="text-muted mr-2">
                    <?= Yii::t('youdate', 'You can upload up to {0} photos', $maxFiles) ?>
                </span>
                <?= Html::submitButton(
                    Icon::fe('file-plus', ['class' => 'mr-2']) . Yii::t('youdate', 'Upload'),
                    ['class' => 'btn btn-primary']
                ) ?>
            <?php else: ?>
                <span class="text-warning mr-2">
                    <?= Yii::t('youdate', 'You have {0} photos', $currentPhotosCount) ?>
                </span>
                <?= Html::button(Icon::fe('file-plus', ['class' => 'mr-2']) . Yii::t('youdate', 'Upload'), [
                    'class' => 'btn btn-primary btn-disabled',
                    'disabled' => 'disabled',
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
    <?php ActiveForm::end() ?>
</div>
