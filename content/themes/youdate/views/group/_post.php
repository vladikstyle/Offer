<?php

use youdate\widgets\PostWidget;
use app\helpers\Url;

/** @var $model \app\models\Post */
/** @var $group \app\models\Group */
/** @var $groupUser \app\models\GroupUser */
/** @var $user \app\models\User */
/** @var $canManage bool */

echo PostWidget::widget([
    'post' => $model,
    'canDelete' => $user && ($model->user_id == $user->id || $canManage),
    'reportUrl' => Url::to(['report-post', 'alias' => $group->alias, 'postId' => $model->id]),
    'deleteUrl' => Url::to(['delete-post', 'alias' => $group->alias, 'postId' => $model->id]),
]);
