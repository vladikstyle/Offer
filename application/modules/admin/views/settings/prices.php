<?php

use app\helpers\Url;
use app\helpers\Html;
use app\settings\SettingsForm;
use app\modules\admin\widgets\DynamicFormWidget;
use yii\widgets\ActiveForm;

/** @var $settingsManager \app\settings\SettingsManager */
/** @var $settingsModel \app\settings\SettingsModel */
/** @var $this \yii\web\View */
/** @var $title string */
/** @var $prices \app\models\Price[] */

$title = Yii::t('app', 'Settings');
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => Url::current()];

$this->beginContent('@app/modules/admin/views/settings/_layout.php');
?>

<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'dynamic-form-prices', 'action' => ['settings-prices']]); ?>
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Prices') ?></h3>
    </div>
    <div class="box-body">
        <div class="alert alert-info">
            <?= Yii::t('app', 'Discount can be absolute (e.g. 0.99) or percentage (e.g. 20%)') ?>
        </div>
        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'wrapper-prices',
            'widgetBody' => '.container-items',
            'widgetItem' => '.item',
            'limit' => 10,
            'min' => 1,
            'insertButton' => '.add-item',
            'deleteButton' => '.remove-item',
            'model' => count($prices) ? $prices[0] : new \app\models\Price(),
            'template' => $this->render('_price-item', [
                'i' => 0,
                'form' => $form,
                'model' => count($prices) ? $prices[0] : new \app\models\Price(),
            ]),
            'formId' => 'dynamic-form-prices',
            'formFields' => [
                'code',
                'title',
                'format',
            ],
        ]); ?>
        <div class="wrapper-prices">
            <div class="container-items">
                <?php foreach ($prices as $i => $model): ?>
                    <?= $this->render('_price-item', [
                        'i' => $i,
                        'model' => $model,
                        'form' => $form,
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php DynamicFormWidget::end(); ?>
    </div>
    <div class="box-footer">
        <?= Html::submitButton( Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?= SettingsForm::widget([
    'manager' => $settingsManager,
    'model' => $settingsModel,
    'formView' => '@app/modules/admin/views/partials/settings_form',
    'title' => sprintf("%s (%s)", $title, Yii::t('app', 'credits')),
]) ?>

<?php $this->endContent() ?>
