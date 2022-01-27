<?php

use yii\bootstrap\Nav;
use app\modules\admin\helpers\Html;

?>
<div class="box box-solid box-nav">
    <div class="box-body no-padding clearfix">
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <?= Nav::widget([
                    'options' => [
                        'class' => 'nav-pills pull-left',
                    ],
                    'items' => [
                        [
                            'label' => Yii::t('app', 'Bans list'),
                            'url' => ['ban/index']
                        ],
                    ]
                ]) ?>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="box-actions">
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add ban record'), ['create'], [
                        'class' => 'btn btn-sm btn-primary',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
