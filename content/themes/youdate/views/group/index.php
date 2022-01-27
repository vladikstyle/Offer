<?php

use app\helpers\Html;
use youdate\helpers\Icon;
use youdate\widgets\GroupsListView;
use youdate\widgets\EmptyState;

/* @var $this \app\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchQuery string */
/* @var $forCurrentUser string */

$this->title = Yii::t('youdate', 'Groups');
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-group-index';
?>
<div class="page-header">
    <h1 class="page-title">
        <?php if ($forCurrentUser): ?>
            <?= Yii::t('youdate', 'Your groups') ?>
        <?php else: ?>
            <?= Yii::t('youdate', 'Groups') ?>
        <?php endif; ?>
    </h1>
    <div class="page-options d-flex">
        <?= Html::a(Yii::t('youdate', 'Create group'), ['create'], ['class' => 'btn btn-primary mr-2']) ?>
        <?php if ($forCurrentUser): ?>
            <?= Html::a(Yii::t('youdate', 'All groups'), ['group/index'], ['class' => 'btn btn-secondary']) ?>
        <?php else: ?>
            <?= Html::a(Yii::t('youdate', 'Your groups'), ['group/index', 'forCurrentUser' => 1], ['class' => 'btn btn-secondary']) ?>
        <?php endif; ?>
        <?= Html::beginForm($forCurrentUser ? ['index', 'forCurrentUser' => 1] : ['index'], 'get') ?>
            <div class="input-icon ml-2">
                <span class="input-icon-addon">
                    <?= Icon::fe('search') ?>
                </span>
                <input type="text" class="form-control w-10"
                       autocomplete="off"
                       name="q"
                       value="<?= Html::encode($searchQuery) ?>"
                       placeholder="<?= Yii::t('youdate', 'Search group') ?>">
            </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php if ($dataProvider->getTotalCount()): ?>
    <?= GroupsListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_item',
        'itemOptions' => ['tag' => false],
    ]) ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <?= EmptyState::widget([
                'icon' => 'fe fe-grid',
                'title' => Yii::t('youdate', 'Groups not found'),
                'subTitle' => Yii::t('youdate', 'You can try to narrow your search filters'),
            ]) ?>
        </div>
    </div>
<?php endif; ?>
