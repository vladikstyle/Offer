<?php

use youdate\widgets\ActiveForm;
use youdate\widgets\Upload;
use app\helpers\Html;
use app\models\Group;

/** @var \app\models\Group $group */
/** @var \app\base\View $this */
?>
<?= $this->render('/_alert') ?>

<?php $form = ActiveForm::begin([
    'id' => 'group-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>

<?= $form->field($group, 'title')->textInput(['autocomplete' => 'off']) ?>
<?= $form->field($group, 'description')->textarea() ?>

<?php if ($group->visibility !== Group::VISIBILITY_BLOCKED): ?>
    <?= $form->field($group, 'visibility')->dropDownList([
        Group::VISIBILITY_VISIBLE => Yii::t('youdate', 'Visible'),
        Group::VISIBILITY_PRIVATE => Yii::t('youdate', 'Private'),
    ], ['prompt' => '']) ?>
<?php endif; ?>

<div class="row">
    <div class="col-sm-12 col-md-6">
        <?= $form->field($group, 'country')->widget(\youdate\widgets\CountrySelector::class) ?>
    </div>
    <div class="col-sm-12 col-md-6">
        <?= $form->field($group, 'city')->widget(\youdate\widgets\CitySelector::class, [
            'items' => [],
            'preloadedValue' => [
                'value' => $group->city,
                'title' => $group->getCityName(),
                'city' => $group->getCityName(),
                'region' => null,
                'population' => null,
            ],
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
        <?= $form->field($group, 'photo')->widget(Upload::class, [
            'id' => 'photo-upload',
            'url' => ['/default/upload-photo'],
            'multiple' => false,
            'maxFileSize' => $this->frontendSetting('photoMaxFileSize', 20) * 1024 * 1024,
            'maxNumberOfFiles' => 1,
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-4">
        <?= $form->field($group, 'cover')->widget(Upload::class, [
            'id' => 'cover-upload',
            'url' => ['/default/upload-photo'],
            'multiple' => false,
            'maxFileSize' => $this->frontendSetting('photoMaxFileSize', 20) * 1024 * 1024,
            'maxNumberOfFiles' => 1,
        ]) ?>
    </div>
</div>
<?= Html::submitButton($group->isNewRecord ?
    Yii::t('youdate', 'Create') :
    Yii::t('youdate', 'Save'),
    ['class' => 'btn float-right btn-primary']) ?><br>
<?php ActiveForm::end(); ?>
