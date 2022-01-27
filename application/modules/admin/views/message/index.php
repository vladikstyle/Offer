<?php

use app\modules\admin\widgets\UserSearch;
use app\models\Message;
use app\helpers\Html;
use app\helpers\Url;
use yii\grid\GridView;

/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel \app\modules\admin\models\search\MessageSearch */

$this->title = Yii::t('app', 'Manage messages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nav-tabs-custom">
    <div class="tab-content no-padding table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}\n{pager}",
            'tableOptions' => ['class' => 'table table-vcenter'],
            'columns' => [
                [
                    'attribute' => 'fromUserId',
                    'format' => 'raw',
                    'contentOptions' => ['width' => 250, 'style' => 'vertical-align: top !important'],
                    'filter' => UserSearch::widget([
                        'model' => $searchModel,
                        'attribute' => 'fromUserId',
                        'initSelection' => $searchModel->getUserSelection('fromUserId'),
                    ]),
                    'value' => function(Message $model) {
                        return $this->render('/partials/_user_column', [
                            'user' => $model->sender,
                        ]);
                    }
                ],
                [
                    'attribute' => 'toUserId',
                    'format' => 'raw',
                    'contentOptions' => ['width' => 250, 'style' => 'vertical-align: top !important'],
                    'filter' => UserSearch::widget([
                        'model' => $searchModel,
                        'attribute' => 'toUserId',
                        'initSelection' => $searchModel->getUserSelection('toUserId'),
                    ]),
                    'value' => function(Message $model) {
                        return $this->render('/partials/_user_column', [
                            'user' => $model->receiver,
                        ]);
                    }
                ],
                [
                    'attribute' => 'text',
                    'format' => 'raw',
                    'value' => function (Message $model) {
                        return $this->render('_message', [
                            'model' => $model,
                        ]);
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'contentOptions' => ['style' => 'vertical-align: top !important'],
                    'value' => function ($model) {
                        if (extension_loaded('intl')) {
                            return Yii::t('app', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
                        } else {
                            return date('Y-m-d G:i:s', $model->created_at);
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{block} {delete}',
                    'contentOptions' => ['class' => 'text-right', 'width' => 200],
                    'buttons' => [
                        'block' => function ($url, $model, $key) {
                            /** @var $model Message */
                            return Html::a('<span class="fa fa-user-times"></span>', Url::to(['user/block', 'id' => $model->from_user_id]), [
                                'title' => Yii::t('app', 'Block sender'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to block this user?'),
                                'class' => 'btn btn-sm btn-warning',
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to delete this message?'),
                                'class' => 'btn btn-sm btn-danger',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
