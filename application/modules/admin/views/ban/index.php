<?php

use app\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Nav;

/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Manage bans');
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_nav') ?>
<div class="nav-tabs-custom">
    <div class="tab-content no-padding">
        <?php Pjax::begin() ?>
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n{pager}",
                'tableOptions' => ['class' => 'table table-vcenter'],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'contentOptions' => ['width' => 120],
                    ],
                    [
                        'attribute' => 'ip',
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                        'contentOptions' => ['width' => 200],
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'datetime',
                        'contentOptions' => ['width' => 200],
                    ],
                    [
                        'class' => \yii\grid\ActionColumn::class,
                        'template' => '{update} {delete}',
                        'contentOptions' => ['width' => 100],
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a('<span class="fa fa-pencil"></span>', $url, [
                                    'title' => Yii::t('app', 'Update'),
                                    'data-pjax' => 0,
                                    'class' => 'btn btn-sm btn-primary',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<span class="fa fa-trash"></span>', $url, [
                                    'title' => Yii::t('app', 'Delete'),
                                    'data-pjax' => 0,
                                    'data-method' => 'post',
                                    'data-confirm' => Yii::t('app', 'Are you sure want to delete this ban record?'),
                                    'class' => 'btn btn-sm btn-danger',
                                ]);
                            },
                        ]
                    ],
                ],
            ]); ?>
        </div>
        <?php Pjax::end() ?>
    </div>
</div>
