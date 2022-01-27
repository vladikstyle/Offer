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
/** @var $currencies \app\models\Currency[] */

$title = Yii::t('app', 'Settings');
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => Url::current()];

$this->beginContent('@app/modules/admin/views/settings/_layout.php');
?>

<?= SettingsForm::widget([
    'manager' => $settingsManager,
    'model' => $settingsModel,
    'formView' => '@app/modules/admin/views/partials/settings_form',
    'title' => $title,
]) ?>

<div class="box box-primary">
    <?php $form = ActiveForm::begin(['id' => 'dynamic-form-currencies', 'action' => ['settings-currencies']]); ?>
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Currencies') ?></h3>
    </div>
    <div class="box-body">
        <div class="alert alert-success">
            <?= Yii::t('app', 'Check this this page {link} for {code} reference', [
                'link' => Html::a('https://en.wikipedia.org/wiki/ISO_4217', 'https://en.wikipedia.org/wiki/ISO_4217', [
                    'target' => '_blank',
                ]),
                'code' => '<code>code</code>',
            ]) ?>
        </div>
        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'wrapper-currencies',
            'widgetBody' => '.container-items',
            'widgetItem' => '.item',
            'limit' => 10,
            'min' => 1,
            'insertButton' => '.add-item',
            'deleteButton' => '.remove-item',
            'model' => count($currencies) ? $currencies[0] : new \app\models\Currency(),
            'template' => $this->render('_currency-item', [
                'i' => 0,
                'form' => $form,
                'model' => count($currencies) ? $currencies[0] : new \app\models\Currency(),
            ]),
            'formId' => 'dynamic-form-currencies',
            'formFields' => [
                'code',
                'title',
                'format',
            ],
        ]); ?>
        <div class="wrapper-currencies">
            <div class="container-items">
                <?php foreach ($currencies as $i => $model): ?>
                    <?= $this->render('_currency-item', [
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

<?php $this->endContent() ?>
