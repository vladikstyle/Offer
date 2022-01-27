<?php

use app\models\Photo;
use app\helpers\Html;
use youdate\helpers\Icon;

/** @var $model Photo */
/** @var $profile \app\models\Profile */
/** @var $photoModerationEnabled bool */

$profile = Yii::$app->user->identity->profile;
$previewUrl = $model->getThumbnail(300, 300, 'crop-center', ['sharp' => 1]);
?>
<div class="photo-item col-sm-4">
    <div class="card">
        <img class="card-img-top rounded" src="<?= $previewUrl ?>">
        <div class="card-body d-flex flex-column pt-2 pl-0 pr-0 pb-2">
            <div class="d-flex align-items-center mt-auto">
                <?php if ($model->is_verified): ?>
                    <div>
                        <span class="text-muted"><?= Yii::t('youdate', 'Verified') ?></span>
                    </div>
                <?php elseif ($photoModerationEnabled == true && !$model->is_verified): ?>
                    <div>
                        <span class="text-warning bg-orange-darker rounded px-2 py-1"
                              rel="tooltip" title="<?= Yii::t('youdate', 'This photo must be approved by administration') ?>">
                            <?= Icon::fe('alert-circle') ?>
                        </span>
                    </div>
                <?php endif; ?>
                <div class="ml-auto text-right">
                    <?= Html::a(Icon::fe('check'), ['/photo/set-main', 'id' => $model->id], [
                        'class' => 'btn btn-ajax btn-sm btn-' . ($profile->photo_id == $model->id ? 'primary' : 'secondary'),
                        'data-pjax-container' => '#pjax-settings-photos',
                        'data-type' => 'post',
                    ]) ?>
                    <?php if ($model->is_private == false): ?>
                        <?= Html::a(Icon::fe('eye'), ['/photo/toggle-private', 'id' => $model->id], [
                            'class' => 'btn btn-ajax btn-sm btn-success',
                            'data-pjax-container' => '#pjax-settings-photos',
                            'data-type' => 'post',
                            'rel' => 'tooltip',
                        ]) ?>
                    <?php else: ?>
                        <?= Html::a(Icon::fe('eye-off'), ['/photo/toggle-private', 'id' => $model->id], [
                            'class' => 'btn btn-ajax btn-sm btn-warning',
                            'data-pjax-container' => '#pjax-settings-photos',
                            'data-type' => 'post',
                            'rel' => 'tooltip',
                        ]) ?>
                    <?php endif; ?>
                    <?= Html::a(Icon::fe('trash'), ['/photo/delete', 'id' => $model->id], [
                        'class' => 'btn btn-ajax btn-sm btn-danger',
                        'data-pjax-container' => '#pjax-settings-photos',
                        'data-confirm-title' => Yii::t('youdate', 'Delete this photo?'),
                        'data-title' => Yii::t('youdate', 'Delete photo'),
                        'data-type' => 'post',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
