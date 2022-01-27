<?php

use app\helpers\Html;
use app\models\Group;
use app\modules\admin\components\Permission;
use yii\grid\GridView;

/** @var $this \app\base\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel \app\modules\admin\models\search\GroupSearch */

$this->title = Yii::t('app', 'Manage groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box no-border">
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-vcenter'],
            'columns' => [
                [
                    'attribute' => 'photo_path',
                    'format' => 'raw',
                    'filter' => false,
                    'options' => ['width' => 84],
                    'value' => function (Group $group) {
                        return $this->render('/partials/_group_column', [
                            'group' => $group,
                        ]);
                    }
                ],
                [
                    'attribute' => 'title',
                    'label' => Yii::t('app', 'Title'),
                    'format' => 'raw',
                    'value' => function(Group $model) {
                        return
                            Html::tag('div', Html::a(Html::encode($model->title), ['update', 'id' => $model->id]), ['class' => 'text-bold']) .
                            Html::tag('div', Html::encode($model->alias), ['class' => 'text-muted']);
                    }
                ],
                [
                    'attribute' => 'description',
                    'label' => Yii::t('app', 'Description'),
                    'format' => 'raw',
                    'value' => function(Group $model) {
                        return Html::encode($model->getShortDescription(40, false));
                    }
                ],
                [
                    'attribute' => 'username',
                    'label' => Yii::t('app', 'User'),
                    'format' => 'raw',
                    'visible' => $this->getCurrentUser()->hasPermission(Permission::USERS),
                    'value' => function(Group $model) {
                        return $this->render('/partials/_user_column', [
                            'user' => $model->user,
                        ]);
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'value' => function ($model) {
                        if (extension_loaded('intl')) {
                            return Yii::t('app', '{0, date, MMM dd, Y HH:mm}', [$model->created_at]);
                        } else {
                            return date('Y-m-d G:i:s', $model->created_at);
                        }
                    },
                ],
                [
                    'attribute' => 'is_verified',
                    'header' => Yii::t('app', 'Verification'),
                    'filter' => [
                        0 => Yii::t('app', 'Not verified'),
                        1 => Yii::t('app', 'Verified'),
                    ],
                    'format' => 'raw',
                    'value' => function (Group $group) {
                        if ($group->is_verified) {
                            $cssClass = 'success';
                            $label = Yii::t('app', 'Yes');
                        } else {
                            $cssClass = 'default';
                            $label = Yii::t('app', 'No');
                        }

                        return Html::tag('span', $label, ['class' => 'label label-' . $cssClass]);
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {toggle-block} {delete}',
                    'contentOptions' => ['width' => 150, 'class' => 'text-right'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-edit"></span>', $url, [
                                'title' => Yii::t('app', 'Update'),
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-primary',
                            ]);
                        },
                        'toggle-block' => function ($url, $model, $key) {
                            /** @var Group $model */
                            if ($model->visibility === Group::VISIBILITY_BLOCKED) {
                                return Html::a('<span class="fa fa-times-rectangle"></span>', $url, [
                                    'title' => Yii::t('app', 'Unblock group'),
                                    'data-pjax' => 0,
                                    'data-method' => 'post',
                                    'class' => 'btn btn-sm btn-warning',
                                ]);
                            } else {
                                return Html::a('<span class="fa fa-times-rectangle"></span>', $url, [
                                    'title' => Yii::t('app', 'Block group'),
                                    'data-pjax' => 0,
                                    'data-method' => 'post',
                                    'class' => 'btn btn-sm btn-default',
                                ]);
                            }
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to delete this group?'),
                                'class' => 'btn btn-sm btn-danger',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
