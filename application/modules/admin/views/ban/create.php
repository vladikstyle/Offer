<?php

/* @var $this yii\web\View */
/* @var $ban app\models\Ban */

$this->title = Yii::t('app', 'Add ban record');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Manage bans'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_nav') ?>
<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title"><?= $this->title ?></h2>
    </div>
    <div class="box-body">
        <?= $this->render('_form', ['ban' => $ban]) ?>
    </div>
</div>
