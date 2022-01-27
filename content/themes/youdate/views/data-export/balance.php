<?php

use app\helpers\Html;
use app\models\BalanceTransaction;
use youdate\helpers\HtmlHelper;
use youdate\widgets\GridView;

/** @var $content string */
/** @var $title string */
/** @var $balance float */
/** @var $transactionsProvider \yii\data\ActiveDataProvider */


$transactionsProvider->setPagination(false);
$transactionsProvider->setSort(false);
?>
<?php $this->beginContent('@theme/views/data-export/layout.php'); ?>

<div class="row row-cards">
    <div class="col-3">
        <div class="card">
            <div class="card-body p-3 text-center">
                <div class="h2 m-0 mt-1"><?= $balance ?></div>
                <div class="text-muted mb-4"><?= Yii::t('youdate', 'Credits') ?></div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body p-3 text-center">
                <div class="h2 m-0 mt-1"><?= $transactionsProvider->getTotalCount() ?></div>
                <div class="text-muted mb-4"><?= Yii::t('youdate', 'Transactions') ?></div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <?= Yii::t('youdate', 'Transactions') ?>
        </h4>
    </div>
    <?= GridView::widget([
        'dataProvider' => $transactionsProvider,
        'filterUrl' => false,
        'showHeader' => false,
        'layout' => '<div class="table-responsive">{items}</div> {pager}',
        'options' => ['tag' => false],
        'summaryOptions' => ['class' => 'summary p-2'],
        'tableOptions' => ['class' => 'table table-outline table-vcenter text-nowrap card-table transactions-grid-view'],
        'columns' => [
            'transactionType' => [
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-center col-transaction-type'],
                'value' => function (BalanceTransaction $model) {
                    return HtmlHelper::transactionIcon($model->getTransactionInfo());
                }
            ],
            'transactionTitle' => [
                'format' => 'raw',
                'contentOptions' => ['class' => 'expand'],
                'value' => function(BalanceTransaction $model) {
                    $info = $model->getTransactionInfo();
                    if ($info) {
                        return $info->getTitle();
                    } else {
                        return 'unknown';
                    }
                }
            ],
            'transactionAmount' => [
                'format' => 'raw',
                'attribute' => 'amount',
                'contentOptions' => ['class' => 'text-right'],
                'value' => function (BalanceTransaction $model) {
                    if ($model->amount > 0) {
                        return sprintf("<span class=\"transaction-add\">%+d %s</span>",
                            $model->amount,
                            Yii::t('youdate', 'credits')
                        );
                    } else {
                        return sprintf("<span class=\"transaction-remove\">%+d %s</span>",
                            $model->amount,
                            Yii::t('youdate', 'credits')
                        );
                    }
                }
            ],
            'transactionService' => [
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-right'],
                'value' => function (BalanceTransaction $model) {
                    $info = $model->getTransactionInfo();
                    if ($info && $info->getServiceName()) {
                        return '<i class="payment payment-' . $info->getServiceName() . '"></i>';
                    } else {
                        return '';
                    }
                }
            ],
            'transactionDate' => [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'text-muted text-center shrink'],
            ],
        ]
    ]) ?>
</div>
<?php $this->endContent(); ?>
