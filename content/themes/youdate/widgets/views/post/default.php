<?php

use app\helpers\Url;
use app\helpers\Html;
use app\models\PostAttachment;
use hauntd\vote\widgets\Like;
use youdate\helpers\Icon;
use youdate\widgets\Gallery;
use Carbon\Carbon;

/** @var $post \app\models\Post */
/** @var $canDelete bool */
/** @var $reportUrl string */
/** @var $deleteUrl string */

$user = $post->user;
$userProfile = $user->profile;
?>
<div class="post card" data-post-id="<?= $post->id ?>">
    <div class="post-header d-flex flex-row align-items-center">
        <a href="<?= Url::to(['profile/view', 'username' => $user->username]) ?>"
           data-pjax="0"
           class="media-object avatar avatar-md mr-4" style="background-image: url(<?= $userProfile->getAvatarUrl() ?>)"></a>
        <a href="<?= Url::to(['profile/view', 'username' => $user->username]) ?>" data-pjax="0">
            <h5 class="mb-1"><?= Html::encode($userProfile->getDisplayName()) ?></h5>
        </a>
        <div class="ml-auto text-right text-muted" rel="tooltip" title="<?= Yii::$app->formatter->asDatetime($post->created_at) ?>">
            <?= Carbon::createFromTimestampUTC($post->created_at)
                ->locale(Yii::$app->language)
                ->diffForHumans() ?>
        </div>
    </div>
    <div class="post-body card-body">
        <div class="post-content">
            <?= nl2br(Html::encode(trim($post->content))) ?>
        </div>
        <?php if (count($post->attachments)): ?>
            <?= Gallery::widget([
                'items' => array_map(function(PostAttachment $postAttachment) {
                    return [
                        'url' => $postAttachment->getUrl(),
                        'src' => $postAttachment->getThumbnail(600, 400),
                        'options' => [
                            'class' => 'post-attachment-image gallery-item',
                        ]
                    ];
                }, $post->attachments),
                'clientOptions' => [
                    'container' => '#post-attachments-gallery-' . $post->id,
                ],
                'templateOptions' => [
                    'id' => 'post-attachments-gallery-' . $post->id,
                ],
                'options' => [
                    'class' => 'post-attachments d-flex flex-row'
                ],
            ]) ?>
        <?php endif; ?>
    </div>
    <div class="post-footer card-footer d-flex">
        <?= Like::widget([
            'entity' => 'postLike',
            'model' => $post,
            'buttonOptions' => [
                'class' => 'vote-btn btn btn-icon btn-like',
                'icon' => Icon::fa('heart'),
                'label' => false,
            ]
        ]); ?>
        <?php if ($canDelete): ?>
            <div class="dropdown ml-auto">
                <button class="btn btn-icon btn-more dropdown-toggle"
                        type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?= Icon::fe('more-horizontal') ?>
                </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item btn-ajax"
                           data-type="post"
                           data-confirm-title="<?= Yii::t('youdate', 'Do you really want to delete this post?') ?>"
                           data-pjax="0"
                           data-pjax-container="#pjax-group-posts"
                           href="<?= $deleteUrl ?>">
                            <?= Yii::t('youdate', 'Delete post') ?>
                        </a>
                    </div>
            </div>
        <?php endif; ?>
    </div>
</div>
