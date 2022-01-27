<?php

use app\helpers\Html;

/* @var $this \app\base\View */
/* @var $group \app\forms\Group */

$this->title = Yii::t('youdate', 'New group');
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-group-create';
?>
<div class="page-header">
    <h1 class="page-title">
        <?= Html::encode($this->title) ?>
    </h1>
    <div class="page-options d-flex">
        <?= Html::a(Yii::t('youdate', 'All groups'), ['group/index'], ['class' => 'btn btn-secondary mr-2']) ?>
        <?= Html::a(Yii::t('youdate', 'Your groups'), ['group/index', 'forCurrentUser' => 1], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <?= $this->render('_form', ['group' => $group]) ?>
    </div>
</div>
