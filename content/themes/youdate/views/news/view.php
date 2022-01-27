<?php

use app\helpers\Html;
use app\helpers\Url;
use Carbon\Carbon;
use hauntd\vote\widgets\Like;
use youdate\helpers\Icon;
use youdate\widgets\EmptyState;

/* @var $this \app\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $newsModel \app\models\News */
/* @var $latestNews \app\models\News[] */

$this->title = $newsModel->title;
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-news-view';
?>
<div class="row">
    <div class="col-lg-9">
        <div class="news-view card">
            <div class="card-body">
                <div class="text-wrap p-lg-6">
                    <h1 class="mb-2"><?= Html::encode($newsModel->title) ?></h1>
                    <div class="text-muted mb-5">
                        <?= Carbon::createFromTimestampUTC($newsModel->created_at)
                            ->locale(Yii::$app->language)
                            ->diffForHumans() ?>
                    </div>
                    <?= $newsModel->getContentPurified() ?>
                </div>
            </div>
            <div class="card-footer">
                <?= Like::widget([
                    'entity' => 'newsLike',
                    'model' => $newsModel,
                    'buttonOptions' => [
                        'class' => 'vote-btn btn btn-icon btn-like',
                        'icon' => Icon::fa('heart'),
                        'label' => false,
                    ]
                ]); ?>
            </div>
        </div>
    </div>
    <div class="news-sidebar col-lg-3 d-none d-lg-block">
        <?php if (count($latestNews)): ?>
            <div class="d-none d-lg-block mb-3">
                <span class="text-muted"><?= Yii::t('youdate', 'Latest news') ?>:</span>
            </div>
            <?php foreach ($latestNews as $news): ?>
                <a href="<?= Url::to(['view', 'alias' => $news->alias]) ?>" class="latest-news-item">
                    <h5><?= Html::encode($news->title) ?></h5>
                    <div class="excerpt"><?= Html::encode($news->getExcerpt()) ?></div>
                    <small class="date text-muted"><?= Yii::$app->formatter->asDatetime($news->created_at) ?></small>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-block btn-primary mb-6">
            <?= Icon::fe('file-text', ['class' => 'mr-2']) ?>
            <?= Yii::t('youdate', 'See all news') ?>
        </a>
    </div>
</div>
