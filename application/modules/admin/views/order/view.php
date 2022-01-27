<?php

use app\models\Order;
use app\modules\admin\helpers\Html;
use yii\widgets\DetailView;

/** @var $this \app\base\View */
/** @var $model \app\models\Order */

$this->title = Yii::t('app', 'View order');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Manage orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$view = $this;
?>
<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title"><?= Yii::t('app', 'View order #{0}', $model->id) ?></h2>
    </div>
    <div class="box-body no-padding">
        <?= DetailView::widget([
            'model' => $model,
            'options' => [
                'class' => 'table table-stripeddetail-view',
            ],
            'attributes' => [
                'id',
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function(Order $order) {
                        return Html::paymentStatusToLabel($order->status);
                    },
                ],
                [
                    'attribute' => 'user_id',
                    'format' => 'raw',
                    'value' => function(Order $order) {
                        if ($order->user_id == null) {
                            return null;
                        }
                        return Html::a($order->user->profile->getDisplayName(), [
                            'user/info', 'id' => $order->user_id
                        ]);
                    },
                ],
                [
                    'attribute' => 'guid',
                    'format' => 'raw',
                    'value' => function(Order $order) {
                        return Html::tag('code', $order->guid);
                    },
                ],

                [
                    'attribute' => 'payment_method',
                    'format' => 'raw',
                    'value' => function (Order $order) {
                        return Html::paymentMethodToIcon($order->payment_method);
                    },
                ],
                [
                    'attribute' => 'total_amount',
                    'format' => 'raw',
                    'value' => function (Order $order) {
                        return sprintf('%s %s',
                            Html::tag('strong', $order->total_price),
                            Html::tag('span', $order->currency, ['class' => 'text-muted'])
                        );
                    }
                ],
                [
                    'attribute' => 'payment_id',
                    'format' => 'raw',
                    'value' => function(Order $order) {
                        return $order->payment_id !== null ? Html::tag('code', $order->payment_id) : null;
                    },
                ],
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
