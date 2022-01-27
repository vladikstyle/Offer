<?php

/* @var $this \app\base\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use app\helpers\Url;

$this->title = Yii::t('youdate', 'Your IP address has been blocked') ;
$this->context->layout = 'error';
?>
<div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="si si-exclamation"></i> <?= Yii::t('youdate', 'Forbidden') ?></div>
    <h1 class="h2 mb-3"><?= $this->title ?></h1>
    <p class="h4 text-muted font-weight-normal mb-7">
        <?= Yii::t('youdate', 'You have no access to the requested page') ?>
    </p>
</div>
