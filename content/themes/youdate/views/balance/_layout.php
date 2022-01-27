<?php

use app\helpers\Html;
use youdate\helpers\Icon;

/* @var $this \app\base\View */
/* @var $currentBalance array */
/* @var $content string */

$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-balance-index';
?>
<div class="page-content">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <h3 class="page-title mb-5"><?= Yii::t('youdate', 'Balance') ?></h3>
            <?= \youdate\widgets\Sidebar::widget([
                'items' => [
                    [
                        'label' => Yii::t('youdate', 'Services'),
                        'url' => ['/balance/services'],
                        'icon' => 'package',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Transactions'),
                        'url' => ['/balance/transactions'],
                        'icon' => 'user',
                    ],
                    [
                        'label' => Yii::t('youdate', 'Buy credits'),
                        'url' => ['/balance/buy'],
                        'icon' => 'plus',
                    ],
                ],
            ]) ?>
        </div>
        <div class="col-lg-9">
            <div class="card mb-5 p-3 d-flex flex-row align-content-center justify-content-between">
                <div class="card-bg card-bg-blue"></div>
                <div class="card-body no-padding">
                    <div class="d-flex flex-row align-items-center">
                        <div class="stamp stamp-md bg-blue mr-3">
                            <?= Icon::fa('money') ?>
                        </div>
                        <div>
                            <h4 class="m-0" style="margin-top: 2px !important;">
                                <?= $currentBalance ?> <small><?= Yii::t('youdate', 'credits') ?></small>
                            </h4>
                            <small class="text-muted"><?= Yii::t('youdate', 'current balance') ?></small>
                        </div>
                        <?php if (isset($showAddButton)): ?>
                            <div class="ml-auto">
                                <?= Html::a(Icon::fa('plus', ['class' => 'mr-2']) . Yii::t('youdate', 'Buy'), ['buy'], [
                                    'class' => 'btn btn-primary float-right',
                                ]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?= $content ?>
        </div>
    </div>
</div>
