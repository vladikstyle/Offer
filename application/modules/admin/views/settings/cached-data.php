<?php

use app\helpers\Html;

/** @var $settingsManager \app\settings\SettingsManager */
/** @var $settingsModel \app\settings\SettingsModel */
/** @var $this \yii\web\View */
/** @var $title string */
/** @var $isFileCache bool */
/** @var $cachePath string */
/** @var $bundleAssetsPath string */
/** @var $thumbnailsPath string */
?>

<?php $this->beginContent('@app/modules/admin/views/settings/_layout.php') ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Cached data') ?></h3>
    </div>
    <div class="box-body">
        <p class="text-muted"><?= Yii::t('app', 'This action will {0}', Html::tag('strong', Yii::t('app', 'delete'), ['class' => 'text-warning'])) ?>:</p>
        <ul class="list">
            <?php if ($isFileCache): ?>
                <li><?= Yii::t('app', 'Application and framework cache in {0}', Html::tag('code', $cachePath)) ?></li>
            <?php else: ?>
                <li><?= Yii::t('app', 'Application and framework cache') ?></li>
            <?php endif; ?>
            <li><?= Yii::t('app', 'Cached assets (css, js, etc) in {0}', Html::tag('code', $bundleAssetsPath)) ?></li>
        </ul>
    </div>
    <div class="box-footer">
        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Flush data'), ['cached-data', 'type' => 'cache'], [
            'class' => 'btn btn-primary',
            'data-method' => 'post'
        ]) ?>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Photo thumbnails') ?></h3>
    </div>
    <div class="box-body">
        <p class="text-muted"><?= Yii::t('app', 'This action will {0}', Html::tag('strong', Yii::t('app', 'delete'), ['class' => 'text-warning'])) ?>:</p>
        <ul class="list">
            <li><?= Yii::t('app', 'All photo thumbnails in {0}', Html::tag('code', $thumbnailsPath)) ?></li>
        </ul>
        <p class="text-muted"><?= Yii::t('app', 'This action will not {0}', Html::tag('strong', Yii::t('app', 'delete'), ['class' => 'text-success'])) ?>:</p>
        <ul class="list">
            <li><?= Yii::t('app', 'Original photos') ?></li>
        </ul>
    </div>
    <div class="box-footer">
        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Flush photo thumbnails'), ['cached-data', 'type' => 'thumbnails'], [
            'class' => 'btn btn-primary',
            'data-method' => 'post'
        ]) ?>
    </div>
</div>

<?php $this->endContent() ?>
