<?php

use app\helpers\Html;
use youdate\helpers\Icon;

/* @var $this \app\base\View */
/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */
/* @var $canView bool */
/* @var $canManage bool */
/* @var $subPage string */

$this->title = $group->getDisplayTitle() . ' - ' . Yii::t('youdate', 'Info');
?>
<?php $this->beginContent('@theme/views/group/_layout.php', [
    'group' => $group,
    'groupUser' => $groupUser,
    'user' => $user,
    'canView' => $canView,
    'canManage' => $canManage,
    'subPage' => $subPage,
]) ?>
<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= Html::encode($group->getDisplayTitle()) ?>
        </h1>
    </div>
    <div class="card-body">
        <?= nl2br(Html::encode($group->description)) ?>
        <?php if (isset($group->country)): ?>
            <div class="group-location text-muted mt-4">
                <?= Icon::fe('map-pin', ['class' => 'mr-2']) ?>
                <?= Html::encode($group->getDisplayLocation()) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endContent() ?>
