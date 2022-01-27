<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Tabs;
use app\helpers\Html;

/** @var $help \app\models\Help */
/** @var $helpCategories \app\models\HelpCategory[] */
/** @var $languages array */
/** @var $this \app\base\View */

?>

<?php $form = ActiveForm::begin([
    'id' => 'help-form',
    'layout' => 'horizontal',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>

<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title"><?= Yii::t('app', 'General info') ?></h2>
    </div>
    <div class="box-body">
        <?= $form->field($help, 'is_active')->checkbox() ?>
        <?= $form->field($help, 'help_category_id')->dropDownList($helpCategories, ['prompt' => '']) ?>
    </div>
</div>

<?php $translationItems = [
    [
        'label' => Yii::t('app', 'Original'),
        'active' => true,
        'content' => $this->render('_fields', [
            'help' => $help,
            'form' => $form,
            'languageCode' => false,
            'languageTitle' => false,
        ]),
    ]
];
foreach ($languages as $languageCode => $languageTitle) {
    $translationItems[] = [
        'label' => $languageTitle,
        'active' => false,
        'content' => $this->render('_fields', [
            'help' => $help,
            'form' => $form,
            'languageCode' => $languageCode,
            'languageTitle' => $languageTitle,
        ]),
    ];
}
?>

<div class="nav-tabs-custom">
    <?= Tabs::widget(['items' => $translationItems, 'tabContentOptions' => ['style' => 'margin-top: 20px;']]) ?>
</div>

<div class="box box-solid">
    <div class="box-body text-left">
        <?= Html::submitButton(
            $help->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
