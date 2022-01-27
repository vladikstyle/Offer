<?php

use app\helpers\Html;

/** @var $i int */
/** @var $model \app\models\Price */
/** @var $form \yii\widgets\ActiveForm; */
?>
<div class="item">
    <?php if (!$model->isNewRecord): ?>
        <?= Html::activeHiddenInput($model, "[{$i}]id") ?>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, "[{$i}]credits")->textInput() ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, "[{$i}]base_price")->textInput() ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, "[{$i}]discount")->textInput() ?>
        </div>
        <div class="col-sm-2">
            <div class="form-group field-price-3-base_price required">
                <label class="control-label" for="price-<?= $i ?>">
                    <?= Yii::t('app', 'Price per credit') ?>
                </label>
                <input type="text"
                       name="<?= "price-per-credit-$i" ?>"
                       class="form-control" disabled value="<?= $model->getPricePerCredit() ?>" />
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group field-price-3-base_price required">
                <label class="control-label" for="price-<?= $i ?>">
                    <?= Yii::t('app', 'Result price') ?>
                </label>
                <input type="text"
                       name="<?= "price-$i" ?>"
                       class="form-control" disabled value="<?= $model->isNewRecord ? '&mdash;' : $model->getActualPrice(true) ?>" />
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group text-center">
                <div class="control-label">&nbsp;</div>
                <button type="button" class="add-item btn btn-success btn-sm"><i class="glyphicon glyphicon-plus"></i></button>
                <button type="button" class="remove-item btn btn-danger btn-sm"><i class="glyphicon glyphicon-minus"></i></button>
            </div>
        </div>
    </div>
</div>
