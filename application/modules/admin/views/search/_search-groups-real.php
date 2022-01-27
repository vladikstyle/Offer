<?php

/** @var $query string */
/** @var $dataProvider \yii\data\ActiveDataProvider */

echo yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'overflow-x-auto'],
    'itemView' => '_search-result-group',
    'itemOptions' => ['tag' => false],
    'layout' => "{summary}\n<div class='search-results clearfix'>{items}\n</div>{pager}",
    'pager' => ['options' => ['class' => 'pagination clearfix']],
    'emptyTextOptions' => ['class' => 'empty p-2'],
]);
