<?php

use app\helpers\Html;
use app\helpers\Url;
use Carbon\Carbon;
use hauntd\vote\widgets\Like;
use youdate\helpers\Icon;

/** @var $this \app\base\View */
/** @var $model \app\models\News */

$newsUrl = Url::to(['news/view', 'alias' => $model->alias]);
?>
<div class="news-item col-sm-6 col-xl-4">
    <div class="card">
        <?php if (isset($model->photo_source)): ?>
            <a href="<?= $newsUrl ?>">
                <img class="card-img-top" src="<?= $model->getPhotoThumbnail(500, 300) ?>"
                     alt="<?= Html::encode($model->title) ?>">
            </a>
        <?php else: ?>
            <div class="no-image"></div>
        <?php endif; ?>
        <div class="card-body d-flex flex-column">

            <h4><a href="<?= $newsUrl ?>"><?= Html::encode($model->title) ?></a></h4>
            <div class="text-muted">
                <?= Html::encode(trim($model->getExcerpt())) ?>
            </div>
            <div class="d-flex align-items-center pt-3 mt-auto">
                <small class="mr-auto text-muted">
                    <?= Carbon::createFromTimestampUTC($model->created_at)
                        ->locale(Yii::$app->language)
                        ->diffForHumans() ?>
                </small>
                <?= Like::widget([
                    'entity' => 'newsLike',
                    'model' => $model,
                    'buttonOptions' => [
                        'class' => 'vote-btn btn btn-icon btn-like',
                        'icon' => Icon::fa('heart'),
                        'label' => false,
                    ]
                ]); ?>
            </div>
        </div>
    </div>
</div>
