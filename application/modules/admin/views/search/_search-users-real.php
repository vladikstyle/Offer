<?php

/** @var $query string */
/** @var $dataProvider \yii\data\ActiveDataProvider */

?>
<?= \yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'overflow-x-auto'],
    'itemView' => '_search-result-user',
    'itemOptions' => ['tag' => false],
    'layout' => "{summary}\n<div class='search-results clearfix'>{items}\n</div>{pager}",
    'pager' => ['options' => ['class' => 'pagination clearfix']],
    'emptyTextOptions' => ['class' => 'empty p-2'],
]) ?>
