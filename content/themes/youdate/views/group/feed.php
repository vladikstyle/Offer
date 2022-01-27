<?php

use app\helpers\Url;
use youdate\widgets\NewPost;

/* @var $this \app\base\View */
/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */
/* @var $canView bool */
/* @var $canManage bool */
/* @var $subPage string */
/* @var $postForm \app\models\Post */

$this->title = $group->getDisplayTitle();
?>
<?php $this->beginContent('@theme/views/group/_layout.php', [
    'group' => $group,
    'groupUser' => $groupUser,
    'user' => $user,
    'canView' => $canView,
    'canManage' => $canManage,
    'subPage' => $subPage,
]) ?>

<?= NewPost::widget([
    'route' => Url::to(['new-post', 'alias' => $group->alias]),
]) ?>

<?= \youdate\widgets\GroupPostsListView::widget([
    'group' => $group,
    'layout' => "{items}\n{pager}\n",
    'itemView' => '_post',
    'itemOptions' => ['tag' => false],
    'viewParams' => [
        'group' => $group,
        'groupUser' => $groupUser,
        'user' => $user,
        'canManage' => $canManage,
    ],
    'emptyView' => '_empty-feed',
]) ?>

<?php $this->endContent() ?>
