<?php

use app\helpers\Html;

/* @var $this \app\base\View */
/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */
/* @var $canView bool */
/* @var $canManage bool */
/* @var $subPage string */

$this->title = $group->getDisplayTitle() . ' - ' . Yii::t('youdate', 'Members');
?>
<?php $this->beginContent('@theme/views/group/_layout.php', [
    'group' => $group,
    'groupUser' => $groupUser,
    'user' => $user,
    'canView' => $canView,
    'canManage' => $canManage,
    'subPage' => $subPage,
    'showMembersWidget' => false,
]) ?>
<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= Yii::t('youdate', 'Members') ?>
        </h1>
    </div>
    <?= \youdate\widgets\GroupMembersGridView::widget(['group' => $group]) ?>
</div>
<?php $this->endContent() ?>
