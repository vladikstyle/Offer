<?php

use app\helpers\Html;
use youdate\widgets\EmptyState;

/* @var $this \app\base\View */
/* @var $newsDataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('youdate', 'News');
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-news-index';
?>
<div class="page-header">
    <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
</div>
<?php if ($newsDataProvider->getTotalCount()): ?>
    <?= \youdate\widgets\ListView::widget([
        'options' => ['class' => 'news-list-view'],
        'dataProvider' => $newsDataProvider,
        'itemView' => '_item',
        'itemOptions' => ['tag' => false],
        'layout' => '<div class="row row-cards row-deck">{items}</div> {pager}',
    ]) ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <?= EmptyState::widget([
                'icon' => 'fe fe-file-text',
                'title' => Yii::t('youdate', 'News not found'),
                'subTitle' => Yii::t('youdate', 'Soon there will be news'),
            ]) ?>
        </div>
    </div>
<?php endif; ?>
