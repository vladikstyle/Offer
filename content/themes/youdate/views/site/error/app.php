<?php

/* @var $this \app\base\View */
/* @var $name string */
/* @var $message string */
/* @var $exception \app\components\AppException */

use app\helpers\Url;
use app\helpers\Html;
use youdate\helpers\Icon;

$this->title = $name;
$this->context->layout = 'error';
?>
<div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="si si-exclamation"></i> </div>
    <h1 class="h2 mb-3">Oops</h1>
    <p class="h4 text-muted font-weight-normal mb-7">
        <?= Html::encode($exception->getMessage()) ?>
    </p>
    <a class="btn btn-primary" href="<?= Url::to(['/']) ?>">
        <?= Icon::fe('arrow-left', ['class' => 'mr-2']) ?>
        <?= Yii::t('youdate', 'Go back') ?>
    </a>
</div>
