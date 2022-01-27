<?php

use yii\helpers\Html;
use app\helpers\Url;

/** @var $this \app\base\View */

?>
<div class="header py-4">
    <div class="container">
        <div class="d-flex">
            <a class="header-brand" href="<?= Url::to(['/']) ?>">
                <i class="fa fa-heart pr-2" style="color: #f66d9b"></i>
                <span>Custom Logo</span>
            </a>
            <div class="d-flex align-items-center order-lg-2 ml-auto">
                <span class="pr-4"><?= Yii::t('youdate', 'Have an account?') ?></span>
                <?= Html::a(Yii::t('youdate', 'Sign in'), ['/security/login'], [
                    'class' => 'btn btn-pill btn-login',
                    'data-toggle' => 'modal',
                    'data-target' => '#modal-auth',
                ]) ?>
            </div>
        </div>
    </div>
</div>
