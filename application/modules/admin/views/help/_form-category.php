<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Tabs;
use app\helpers\Html;

/** @var $helpCategory \app\models\HelpCategory */
/** @var $languages array */
/** @var $this \app\base\View */

$iconsUrl1 = 'https://feathericons.com/';
$iconsUrl2 = 'https://fontawesome.com/v4.7.0/icons/';
?>

<?php $form = ActiveForm::begin([
    'id' => 'help-category-form',
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
        <div class="row">
            <div class="col-sm-9 col-sm-offset-3">
                <div class="alert alert-info">
                    <?= Yii::t('app', 'For icons use one of these') . ':' ?>
                    <a href="https://feathericons.com/">https://feathericons.com</a>.
                    <?= Yii::t('app', 'Icon name without "fe fe-" prefix. For example') ?>: <code>users</code>
                </div>
            </div>
        </div>

        <?= $form->field($helpCategory, 'is_active')->checkbox() ?>
        <?= $form->field($helpCategory, 'alias') ?>
        <?= $form->field($helpCategory, 'icon') ?>
    </div>
</div>

<?php $translationItems = [
    [
        'label' => Yii::t('app', 'Original'),
        'active' => true,
        'content' => $this->render('_fields-category', [
            'helpCategory' => $helpCategory,
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
        'content' => $this->render('_fields-category', [
            'helpCategory' => $helpCategory,
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
            $helpCategory->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
