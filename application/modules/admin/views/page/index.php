<?php

use app\helpers\Url;
use app\helpers\Html;

/** @var $this \yii\web\View */
/** @var $pages array */
/** @var $currentPage string */
/** @var $content string */
/** @var $pagesEditable bool */
/** @var $language string */
/** @var $languages array */
/** @var $newPageForm \app\modules\admin\forms\NewPageForm */

$this->title = Yii::t('app', 'Manage pages');
$this->params['breadcrumbs'][] = $this->title;
$currentPageUrl = Url::to(['/site/page', 'view' => basename($currentPage, '.php')], true);
?>
<div class="row">
    <div class="col-xs-12 col-md-3">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Pages') ?></h3>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-pills nav-stacked">
                    <?php foreach ($pages as $page): ?>
                        <li class="<?= $currentPage == basename($page) ? 'active' : '' ?> ">
                            <a href="<?= Url::to(['index', 'currentPage' => basename($page)]) ?>">
                                <i class="fa fa-edit"></i> <?= basename($page) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-9">
        <?php $form = \yii\widgets\ActiveForm::begin([
            'action' => ['save', 'currentPage' => $currentPage, 'language' => $language],
            'method' => 'post',
            'options' => ['class' => 'd-inline'],
        ]) ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Editor') ?></h3>
                <div class="box-tools pull-right">
                    <?php if ($currentPage !== null): ?>
                        <?= Html::dropDownList('language', $language, $languages, [
                            'prompt' => Yii::t('app', 'Original'),
                            'class' => 'form-control pages-language-picker',
                            'data-initial-language' => $language,
                            'data-route' => Url::to(['index', 'currentPage' => $currentPage]),
                            'data-unsaved-warning' => Yii::t('app', 'You have edited current page. Proceed without saving?'),
                        ]) ?>
                        <?= Html::submitButton(Yii::t('app', 'Save'), [
                            'class' => 'btn btn-primary',
                        ]) ?>
                    <?php endif; ?>
                    <?= Html::button(Yii::t('app', 'New page'), [
                        'class' => 'btn btn-default',
                        'type' => 'button',
                        'data-toggle' => 'modal',
                        'data-target' => '#new-page',
                    ]) ?>
                    <?= Html::a(Yii::t('app', 'Reset pages'), ['reset'], [
                        'class' => 'btn btn-danger',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('app', 'Do you really want to restore pages from theme files?'),
                    ]) ?>
                </div>
            </div>
            <div class="box-body" style="min-height: 200px;">
                <?php if ($currentPage !== null): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-eye"></i> <?= Yii::t('app', 'Page URL') ?>: <?= Html::a($currentPageUrl, $currentPageUrl, ['target' => '_blank']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!$pagesEditable): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <?= Yii::t('app', 'Page editing is disabled by app config') ?>
                    </div>
                <?php endif; ?>
                <?php if ($currentPage !== null): ?>
                    <?= \trntv\aceeditor\AceEditor::widget([
                        'id' => 'pages',
                        'name' => 'content',
                        'mode' => 'php',
                        'value' => $content,
                        'options' => ['id' => 'editor'],
                    ]) ?>
                    <div class="text-muted mt-2">
                        <?= Yii::t('app', 'Warning. Edit these files carefully') ?>
                    </div>
                <?php else: ?>
                    <p><?= Yii::t('app', 'Choose file to edit') ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php \yii\widgets\ActiveForm::end() ?>
    </div>
</div>

<div class="modal fade" id="new-page"
     tabindex="-1" role="dialog" aria-labelledby="new-page-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php $form = \yii\widgets\ActiveForm::begin([
                'action' => ['create'],
                'method' => 'post',
                'options' => ['class' => 'd-inline'],
            ]) ?>
            <div class="modal-header">
                <h5 class="modal-title" id="new-page-title">
                    <?= Yii::t('app', 'New page') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <?= $form->field($newPageForm, 'pageTitle') ?>
                <?= $form->field($newPageForm, 'fileName')->hint(Yii::t('app', 'A-Z, a-z, 0-9 and dashes')) ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('app', 'Create'), [
                    'class' => 'btn btn-primary',
                ]) ?>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= Yii::t('app', 'Cancel') ?>
                </button>
            </div>
            <?php \yii\widgets\ActiveForm::end() ?>
        </div>
    </div>
</div>
