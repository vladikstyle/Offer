<?php

/* @var $this yii\web\View */
/* @var $newsModel \app\models\News */

$this->title = Yii::t('app', 'Create news post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Manage news'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-solid">
    <div class="box-body">
        <div class="news-create">
            <?= $this->render('_form', [
                'newsModel' => $newsModel,
            ]) ?>
        </div>
    </div>
</div>
