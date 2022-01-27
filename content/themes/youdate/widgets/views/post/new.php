<?php

use youdate\helpers\Icon;
use youdate\widgets\ActiveForm;
use youdate\widgets\Upload;
use yii\helpers\ArrayHelper;
use app\helpers\Html;

/** @var $postForm \app\forms\PostForm */
/** @var $route string */
/** @var $settings array */

?>
<?php $form = ActiveForm::begin([
    'id' => 'post-form',
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
    'action' => $route,
]); ?>
<div class="card post-new">
    <div class="card-body">
        <?= $form->errorSummary($postForm) ?>
        <?= $form->field($postForm, 'content')
            ->textarea([
                'class' => 'form-control post-content-field',
                'rows' => 1,
                'placeholder' => Yii::t('youdate', 'New post'),
            ])
            ->label(false) ?>
    </div>
    <div class="card-footer">
        <?= Upload::widget([
            'id' => 'attachments-upload',
            'url' => ['/default/upload-photo'],
            'model' => $postForm,
            'attribute' => 'attachments',
            'multiple' => true,
            'sortable' => false,
            'maxFileSize' => ArrayHelper::getValue($settings, 'photoMaxFileSize', 20) * 1024 * 1024,
            'maxNumberOfFiles' => ArrayHelper::getValue($settings, 'photoMaxFiles', 10),
            'clientOptions' => [
                'uploadButtonContent' =>
                    '<div class="btn-label">' .
                    Icon::fa('image', ['class' => 'mr-2']) . Yii::t('youdate', 'Upload') .
                    '</div>',
            ],
            'options' => ['class' => 'upload'],
            'buttonsWrapperOptions' => ['class' => 'd-flex'],
            'buttonsAppendHtml' => Html::submitButton(
                Yii::t('youdate', 'Create'),
                ['class' => 'btn ml-auto btn-primary']
            ),
            'filesHtml' => Html::tag('ul', '', ['class' => 'files']),
        ]) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
