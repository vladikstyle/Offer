<?php

use app\helpers\Html;
use app\models\DataRequest;
use youdate\widgets\ActiveForm;
use youdate\helpers\Icon;

/** @var $this \app\base\View */
/** @var $dataRequestForm \app\forms\DataRequestForm */
/** @var $dataRequests \app\models\DataRequest[] */
/** @var $profile \app\models\Profile */

$this->title = Yii::t('youdate', 'Your data');
$this->params['breadcrumbs'][] = $this->title;
$premiumFeaturesEnabled = \yii\helpers\ArrayHelper::getValue($this->params, 'site.premiumFeatures.enabled');
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('youdate', 'Download your data') ?></h3>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'data-request-form',
            'options' => ['class' => 'form-horizontal'],
            'action' => ['request-data'],
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'validateOnBlur' => false,
        ]); ?>
        <?= $this->render('/_alert') ?>
        <?php if (Yii::$app->session->hasFlash('data-request')): ?>
            <div class="text-wrap mb-3">
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <?= Html::encode(Yii::$app->session->getFlash('data-request', null, true)) ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="alert alert-info">
            <ul class="m-0 pl-4">
                <li><?= Yii::t('youdate', 'You can download your information in an HTML format that is easy to view, or a JSON format.') ?></li>
                <li><?= Yii::t('youdate', 'When the archive is ready, you\'ll receive an e-mail notification with a link to download.') ?></li>
                <li><?= Yii::t('youdate', 'Please note that links will be available for download for a few days.') ?></li>
            </ul>
        </div>
        <div>
            <div class="form-label"><?= Yii::t('youdate', 'Data format') ?>:</div>
            <?php foreach ($dataRequestForm->getFormatList() as $value => $title): ?>
                <label class="custom-control custom-radio custom-control-inline">
                    <?= Html::activeRadio($dataRequestForm, 'format', [
                        'class' => 'custom-control-input',
                        'value' => $value,
                        'label' => false,
                        'uncheck' => false,
                    ]) ?>
                    <span class="custom-control-label"><?= $title ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="pt-2">
            <?= Html::submitButton(Yii::t('youdate', 'Request data archive'), [
                'class' => 'btn btn-primary btn-lg',
            ]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php if (count($dataRequests)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Yii::t('youdate', 'Data requests and archives') ?></h3>
        </div>
        <table class="table card-table">
            <tbody>
            <?php foreach ($dataRequests as $dataRequest): ?>
                <tr>
                    <td>
                        <?= Icon::fa('archive', ['class' => 'text-muted mr-2']) ?>
                        <?php $fileName = sprintf('%s %s %s.zip',
                            Yii::t('youdate', 'Data'),
                            $profile->getDisplayName(),
                            $dataRequest->getRequestDate()
                        ) ?>
                        <?php if ($dataRequest->status == DataRequest::STATUS_DONE): ?>
                            <?= Html::a($fileName, ['download-data', 'code' => $dataRequest->code]) ?>
                        <?php else: ?>
                            <?= $fileName ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?= \youdate\helpers\HtmlHelper::dataRequestStatusToBadge($dataRequest->status) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('youdate', 'Delete account') ?></h3>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <strong><?= Yii::t('youdate', 'Warning') ?>:</strong>
            <?= Yii::t('youdate', 'This action will remove all your data completely.') ?>
        </div>
        <div class="text-wrap pt-2 mb-5">
            <?= Yii::t('youdate', 'Data to be removed') ?>:
            <ul class="pt-2">
                <li><?= Yii::t('youdate', 'Your profile info') ?></li>
                <li><?= Yii::t('youdate', 'Your photos') ?></li>
                <li><?= Yii::t('youdate', 'Your messages') ?></li>
                <li><?= Yii::t('youdate', 'Your likes and connections') ?></li>
                <?php if ($premiumFeaturesEnabled): ?>
                <li><?= Yii::t('youdate', 'Your balance and transactions') ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="pt-2">
            <?= Html::a(Yii::t('youdate', 'Delete account'), ['/settings/delete'], [
                'class' => 'btn btn-danger btn-lg',
                'data-method' => 'post',
                'data-confirm' => Yii::t('youdate', 'Are you sure you want to delete your profile?'),
            ]) ?>
        </div>
    </div>
</div>
