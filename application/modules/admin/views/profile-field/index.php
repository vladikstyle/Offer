<?php

use app\helpers\Url;
use app\helpers\Html;
use app\models\ProfileField;
use app\models\ProfileFieldCategory;
use yii\grid\GridView;
use dosamigos\grid\columns\ToggleColumn;
use kotchuprik\sortable\grid\Column as SortingColumn;

/** @var $this \yii\web\View */
/** @var $title string */
/** @var $dataProvider \yii\data\ActiveDataProvider */

$title = Yii::t('app', 'Profile fields');
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => Url::current()];
?>
<div class="box">
    <div class="box-header with-border">
        <?= Html::a(Yii::t('app', 'New field'), ['create'], ['class' => 'btn btn-primary btn-sm pull-right']) ?>
    </div>
    <div class="box-content">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n{items}\n{pager}",
                'tableOptions' => ['class' => 'table table-vcenter'],
                'options' => [
                    'class' => 'grid-view',
                    'data' => [
                        'sortable-widget' => 1,
                        'sortable-url' => Url::toRoute(['sorting']),
                    ]
                ],
                'rowOptions' => function (ProfileField $profileField, $key, $index, $grid) {
                    return [
                        'data-sortable-id' => $profileField->id,
                        'class' => 'bg-white',
                    ];
                },
                'columns' => [
                    [
                        'class' => SortingColumn::class,
                    ],
                    'id',
                    [
                        'attribute' => 'category.title',
                        'label' => Yii::t('app', 'Category'),
                        'value' => function(ProfileField $model) {
                            return Html::encode(Yii::t($model->category->language_category, $model->category->title));
                        },
                    ],
                    [
                        'attribute' => 'title',
                        'label' => Yii::t('app', 'Title'),
                        'value' => function(ProfileField $model) {
                            return Html::encode(Yii::t($model->language_category, $model->title));
                        },
                    ],
                    [
                        'attribute' => 'field_class',
                        'format' => 'raw',
                        'value' => function(\app\models\ProfileField $model) {
                            return '<code>' . $model->field_class . '</code>';
                        }
                    ],
                    [
                        'attribute' => 'alias',
                        'format' => 'raw',
                        'value' => function(\app\models\ProfileField $model) {
                            return '<code>' . $model->alias . '</code>';
                        }
                    ],
                    [
                        'class' => ToggleColumn::class,
                        'attribute' => 'is_visible',
                        'onLabel' => Yii::t('app', 'Visible'),
                        'offLabel' => Yii::t('app', 'Hidden'),
                        'onIcon' => 'fa fa-check-square-o',
                        'offIcon' => 'fa fa-square-o',
                        'contentOptions' => ['class' => 'text-center'],
                        'url' => ['toggle'],
                        'filter' => [
                            ProfileFieldCategory::IS_HIDDEN => Yii::t('app', 'Hidden'),
                            ProfileFieldCategory::IS_VISIBLE => Yii::t('app', 'Visible'),
                        ]
                    ],
                    [
                        'class' => ToggleColumn::class,
                        'attribute' => 'searchable',
                        'label' => Yii::t('app', 'Search'),
                        'onLabel' => Yii::t('app', 'Searchable'),
                        'offLabel' => Yii::t('app', 'Not searchable'),
                        'onIcon' => 'fa fa-check-square-o',
                        'offIcon' => 'fa fa-square-o',
                        'contentOptions' => ['class' => 'text-center'],
                        'url' => ['toggle'],
                        'filter' => [
                            false => Yii::t('app', 'Not searchable'),
                            true => Yii::t('app', 'Searchable'),
                        ]
                    ],
                    [
                        'class' => ToggleColumn::class,
                        'attribute' => 'searchable_premium',
                        'label' => Yii::t('app', 'Search (premium)'),
                        'onLabel' => Yii::t('app', 'Searchable'),
                        'offLabel' => Yii::t('app', 'Not searchable'),
                        'onIcon' => 'fa fa-check-square-o',
                        'offIcon' => 'fa fa-square-o',
                        'contentOptions' => ['class' => 'text-center'],
                        'url' => ['toggle'],
                        'filter' => [
                            false => Yii::t('app', 'Not searchable'),
                            true => Yii::t('app', 'Searchable'),
                        ]
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'contentOptions' => ['class' => 'text-right'],
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
                                    'data-confirm' => Yii::t('app', 'Are you sure want to delete this user?'),
                                    'class' => 'btn btn-sm btn-danger',
                                ]);
                            },
                        ]
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
