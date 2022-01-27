<?php

use app\modules\admin\helpers\Html;

?>
<div class="box box-solid">
    <div class="box-body clearfix">
        <?= Html::a('<i class="fa fa-list"></i> ' . Yii::t('app', 'Items'), ['index'], ['class' => 'btn btn-default btn-sm']) ?>
        <?= Html::a('<i class="fa fa-list"></i> ' . Yii::t('app', 'Categories'), ['categories'], ['class' => 'btn btn-default btn-sm']) ?>
        <div class="card-actions pull-right">
            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create item'), ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create category'), ['create-category'], ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
</div>
