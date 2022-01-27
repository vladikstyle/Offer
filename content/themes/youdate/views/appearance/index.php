<?php

use app\helpers\Html;
use app\helpers\Url;
use youdate\widgets\ActiveForm;

/** @var $modes array */
/** @var $darkMode string */
/** @var $this \app\base\View */

$this->title = Yii::t('youdate', 'Appearance');
$this->registerJs('
    $("#dark-mode-form .imagecheck").click(function(event) {
        var $form = $(this).closest("form"),
            $this = $(this);
        $form.find(".imagecheck-input.active").removeClass("active");
        $this.addClass("active");
        $this.find(".loader").removeClass("hidden");
        $(this).closest("form").submit();
    });
')
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="card-body mode-switcher">
        <?= $this->render('/_alert') ?>
        <?php $form = ActiveForm::begin([
            'id' => 'dark-mode-form',
            'action' => ['change'],
            'method' => 'post',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
            'validateOnBlur' => false,
        ]); ?>
        <div class="row">
            <?php foreach ($modes as $mode => $title): ?>
                <div class="mode">
                    <label class="imagecheck d-flex flex-col mb-4 d-sm-block mx-2">
                        <input name="mode" type="radio" value="<?= $mode ?>"
                               class="imagecheck-input <?= $darkMode == $mode ? 'active' : '' ?>"
                            <?= $darkMode == $mode ? 'checked' : '' ?>>
                        <figure class="imagecheck-figure">
                            <img src="<?= Url::to("@themeUrl/static/images/mode-switcher-$mode.png") ?>" class="imagecheck-image">
                        </figure>
                        <div class="loader hidden"></div>
                    </label>
                    <div class="mode-name">
                        <?= Html::encode($title) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
