<?php

use youdate\helpers\Icon;
use youdate\widgets\DirectoryListView;
use app\helpers\Html;

/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $type string */
/** @var $this \app\base\View */
/** @var $counters array */
/** @var $likesLocked boolean */

$this->title = Yii::t('youdate', 'Likes');
$this->context->layout = 'page-main';

$this->beginContent('@theme/views/connections/_layout.php', [
    'counters' => $counters,
]);
?>

<?php if ($likesLocked): ?>
<div class="card">
    <div class="card-bg card-bg-purple"></div>
    <div class="card-body d-flex align-items-center">
        <?= Icon::fa('lock', ['class' => 'text-yellow mr-2']) ?>
        <h4 class="text-gray font-weight-normal mb-0"><?= Yii::t('youdate', 'You need premium account to unlock this page') ?></h4>
        <?= Html::a(Yii::t('youdate', 'Premium settings'),
            ['balance/services'],
            ['class' => 'btn btn-primary ml-auto']
        ) ?>
    </div>
</div>
<?php endif; ?>

<?= DirectoryListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => $likesLocked ? '_item_locked' : '_item',
    'itemOptions' => ['tag' => false],
    'emptyView' => '_empty_likes',
    'emptyViewParams' => [
        'type' => $type,
    ],
]) ?>

<?php $this->endContent() ?>
