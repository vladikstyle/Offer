<?php

use app\modules\admin\helpers\Html;
use app\modules\admin\widgets\UserSearch;
use app\models\Order;
use yii\grid\GridView;

/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel \app\modules\admin\models\search\OrderSearch */

$this->title = Yii::t('app', 'Manage orders');
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
                    'attribute' => 'user_id',
                    'label' => Yii::t('app', 'User'),
                    'format' => 'raw',
                    'options' => ['width' => 300],
                    'filter' => UserSearch::widget([
                        'model' => $searchModel,
                        'attribute' => 'user_id',
                        'initSelection' => $searchModel->getUserSelection(),
                    ]),
                    'value' => function(\app\models\Order $order) {
                        return $this->render('/partials/_user_column', [
                            'user' => $order->user,
                        ]);
                    }
                ],
                [
                    'attribute' => 'guid',
                    'format' => 'raw',
                    'options' => ['width' => 300],
                    'headerOptions' => ['class' => 'visible-lg'],
                    'filterOptions' => ['class' => 'visible-lg'],
                    'contentOptions' => ['class' => 'visible-lg'],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off'],
                    'value' => function(\app\models\Order $order) {
                        return Html::tag('code', $order->guid, ['class' => 'small bg-gray']);
                    }
                ],
                [
                    'attribute' => 'amount',
                    'label' => Yii::t('app', 'Credits'),
                    'format' => 'raw',
                    'options' => ['width' => 100],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off', 'type' => 'number'],
                    'value' => function (Order $order) {
                        return Html::tag('span', $order->amount, ['class' => 'label label-info']);
                    }
                ],
                [
                    'attribute' => 'total_price',
                    'format' => 'raw',
                    'options' => ['width' => 100],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off', 'type' => 'number'],
                    'value' => function (Order $order) {
                        return sprintf('%s %s',
                            Html::tag('strong', $order->total_price),
                            Html::tag('span', $order->currency, ['class' => 'text-muted'])
                        );
                    }
                ],
                'updated_at:datetime',
                [
                    'attribute' => 'status',
                    'options' => ['width' => 120],
                    'format' => 'raw',
                    'filter' => Html::activeDropDownList($searchModel, 'status',
                        array_combine(Order::$statuses, Order::$statuses), [
                            'class'=>'form-control',
                            'prompt' => ''
                        ]
                    ),
                    'value' => function(\app\models\Order $order) {
                        return Html::paymentStatusToLabel($order->status);
                    }
                ],
                [
                    'attribute' => 'payment_method',
                    'format' => 'raw',
                    'contentOptions' => ['width' => 100],
                    'filter' => Html::activeDropDownList($searchModel, 'payment_method', $searchModel->getPaymentMethodsLabels(), [
                        'prompt' => '',
                        'class' => 'form-control',
                    ]),
                    'value' => function (Order $order) {
                        return Html::paymentMethodToIcon($order->payment_method);
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'contentOptions' => ['class' => 'text-right'],
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-eye"></span>', $url, [
                                'title' => Yii::t('app', 'View'),
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-default',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
