<?php

use app\helpers\Html;
use app\helpers\Url;

/* @var $this yii\web\View */
/* @var $newsModel \app\models\News */

$this->title = Yii::t('app', 'Update news post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Manage news'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('actionButtons') ?>
<?= Html::a(
    '<i class="fa fa-eye"></i> ' . Yii::t('app', 'View on website'),
    Url::to(['/news/view', 'alias' => $newsModel->alias]),
    ['class' => 'btn btn-primary btn-sm', 'target' => '_blank']
) ?>
<?php $this->endBlock() ?>

<div class="box box-solid">
    <div class="box-body">
        <div class="news-create">
            <?= $this->render('_form', [
                'newsModel' => $newsModel,
            ]) ?>
        </div>
    </div>
</div>
