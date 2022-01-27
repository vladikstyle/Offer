<?php

use app\helpers\Html;
use yii\grid\GridView;
use dosamigos\grid\columns\ToggleColumn;

/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Manage help categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_navigation') ?>
<div class="box no-border">
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{pager}",
            'tableOptions' => ['class' => 'table table-vcenter'],
            'rowOptions' => function ($model, $key, $index, $grid) {
                return [
                    'data-sortable-id' => $model->id,
                    'class' => 'bg-white',
                ];
            },
            'columns' => [
                [
                    'class' => \kotchuprik\sortable\grid\Column::class,
                ],
                'title',
                'alias',
                'icon',
                [
                    'class' => ToggleColumn::class,
                    'attribute' => 'is_active',
                    'url' => ['toggle-category'],
                    'onLabel' => Yii::t('app', 'Active'),
                    'offLabel' => Yii::t('app', 'Not active'),
                    'onIcon' => 'fa fa-check-square-o',
                    'offIcon' => 'fa fa-square-o',
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => [
                        0 => Yii::t('app', 'Not active'),
                        1 => Yii::t('app', 'Active'),
                    ]
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update-category} {delete-category}',
                    'contentOptions' => ['class' => 'text-right', 'width' => 200],
                    'buttons' => [
                        'update-category' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-pencil"></span>', $url, [
                                'title' => Yii::t('app', 'Update'),
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-primary',
                            ]);
                        },
                        'delete-category' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to delete this help category?'),
                                'class' => 'btn btn-sm btn-danger',
                            ]);
                        },
                    ]
                ],
            ],
            'options' => [
                'class' => 'grid-view',
                'data' => [
                    'sortable-widget' => 1,
                    'sortable-url' => \yii\helpers\Url::toRoute(['sorting-category']),
                ]
            ],
        ]); ?>
    </div>
</div>
