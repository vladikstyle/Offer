<?php

use yii\widgets\ActiveForm;
use app\helpers\Html;

/* @var $this yii\web\View */
/* @var $ban app\models\Ban */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="ban-form">
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
    ]); ?>
    <?= $form->errorSummary($ban) ?>
    <div class="row">
        <div class="col-xs-12 col-lg-4">
            <?= $form->field($ban, 'ip')
                ->textInput(['autocomplete' => 'off'])
                ->hint(Yii::t('app', 'Example: 192.168.0.130, 10.0.0.0/24')) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton($ban->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
