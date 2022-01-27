<?php

use app\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $newsModel app\models\News */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="news-form">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

    <?= $form->errorSummary($newsModel) ?>

    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <?= $form->field($newsModel, 'title')->textInput(['autocomplete' => 'off']) ?>
        </div>
        <div class="col-xs-12 col-sm-6">
            <?= $form->field($newsModel, 'status')->dropDownList($newsModel->getStatusOptions(), ['prompt' => '']) ?>
        </div>
    </div>

    <?= $this->render('/partials/field-editor', [
        'form' => $form,
        'model' => $newsModel,
        'attribute' => 'content',
    ]) ?>

    <?= $form->field($newsModel, 'excerpt')->textarea() ?>

    <?= $form->field($newsModel, 'photo')->widget(\trntv\filekit\widget\Upload::class, [
        'url' => ['default/upload-photo'],
        'sortable' => false,
        'maxFileSize' => 10 * 1024 * 1024,
        'maxNumberOfFiles' => 1,
    ]) ?>

    <?= $form->field($newsModel, 'is_important')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($newsModel->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
