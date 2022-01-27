<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use app\helpers\Url;
use app\helpers\Html;
use app\models\News;

/* @var $this \yii\web\View */
/* @var $newsDataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\admin\models\search\NewsSearch */

$this->title = Yii::t('app', 'Manage news');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('actionButtons') ?>
<?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
<?php $this->endBlock() ?>

<div class="box no-border">
    <?php Pjax::begin(['id' => 'pjax-news']) ?>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $newsDataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-vcenter'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'options' => ['width' => 100],
                    'filterInputOptions' => ['autocomplete' => 'off', 'class' => 'form-control'],
                ],
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'filterInputOptions' => ['autocomplete' => 'off', 'class' => 'form-control'],
                    'value' => function(News $newsModel) {
                        return
                            Html::a(Html::encode($newsModel->title), ['update', 'id' => $newsModel->id]) .
                            Html::tag('div', Html::encode($newsModel->alias), ['class' => 'text-muted']);
                    }
                ],
                [
                    'attribute' => 'excerpt',
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'filter' => $searchModel->getStatusOptions(),
                    'value' => function (News $news) {
                        $cssClass = 'default';
                        switch ($news->status) {
                            case News::STATUS_PUBLISHED:
                                $cssClass = 'primary';
                                break;
                            case News::STATUS_DRAFT:
                                $cssClass = 'success';
                                break;
                        }
                        return Html::tag('span', $news->status, ['class' => 'label label-' . $cssClass]);
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'options' => ['width' => 150],
                    'contentOptions' => ['class' => 'text-right'],
                    'buttons' => [
                        'view' => function ($url, News $newsModel, $key) {
                            return Html::a('<span class="fa fa-eye"></span>',
                                ['/news/view', 'alias' => $newsModel->alias],
                                ['title' => Yii::t('app', 'View on website'),
                                    'data-pjax' => 0,
                                    'class' => 'btn btn-sm btn-info',
                                    'target' => '_blank',
                                ]);
                        },
                        'update' => function ($url, $newsModel, $key) {
                            return Html::a('<span class="fa fa-pencil"></span>', $url, [
                                'title' => Yii::t('app', 'Update'),
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-default',
                            ]);
                        },
                        'delete' => function ($url, $newsModel, $key) {
                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to delete this user?'),
                                'class' => 'btn btn-sm btn-danger',
                            ]);
                        },
                    ],
                ],
            ],
        ]) ?>
    </div>
    <?php Pjax::end() ?>
</div>
