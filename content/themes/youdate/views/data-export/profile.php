<?php

use app\helpers\Html;

/** @var $content string */
/** @var $title string */
/** @var $profile \app\models\Profile */

?>
<?php $this->beginContent('@theme/views/data-export/layout.php'); ?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <?= Html::encode($title) ?>
        </h4>
    </div>
    <div class="card-body">
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'User ID') ?>:</div>
            <div class="col-6 value"><?= $profile->user_id ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Name') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->name) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Sex') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->getSexTitle()) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Status') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->getStatusTitle()) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Birthday') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->dob) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Looking for') ?>:</div>
            <div class="col-6 value">
                <?= Html::encode($profile->getLookingForTitle()) ?>,
                <?= Html::encode($profile->getLookingForAgeTitle()) ?>
            </div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Timezone') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->timezone) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Country') ?>:</div>
            <div class="col-6 value"><?= Yii::$app->geographer->getCountryName($profile->country) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'City') ?>:</div>
            <div class="col-6 value"><?= Yii::$app->geographer->getCityName($profile->city) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Latitude') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->latitude) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Longitude') ?>:</div>
            <div class="col-6 value"><?= Html::encode($profile->longitude) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Verified') ?>:</div>
            <div class="col-6 value"><?= Yii::$app->formatter->asBoolean($profile->is_verified) ?></div>
        </div>
        <div class="row info-row">
            <div class="col-6 heading"><?= Yii::t('app', 'Language') ?>:</div>
            <div class="col-6 value"><?= $profile->language_id ?></div>
        </div>
        <?php foreach ($profile->getExtraCategories() as $categoryAlias => $categoryTitle): ?>
            <?php foreach ($profile->getExtraFields($profile->user_id, $categoryAlias) as $item): ?>
                <div class="row info-row">
                    <div class="col-6 heading">
                        <?= Html::encode(Yii::t($item['field']->language_category, $item['field']->title)) ?>:
                    </div>
                    <div class="col-6 value">
                        <?php if (isset($item['extra']->value)): ?>
                            <?= $item['field']->formatValue($item['extra']->value) ?>
                        <?php else: ?>
                            <?= Yii::t('youdate', 'Not set') ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="info-row">
            <div class="heading"><?= Yii::t('app', 'Description') ?>:</div>
            <div><?= Html::encode($profile->description) ?></div>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>
