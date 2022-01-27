<?php

use app\helpers\Html;
use app\helpers\Url;
use app\controllers\GroupController;
use youdate\widgets\EmptyState;

/** @var $group \app\models\Group */
/** @var $randomMembers \app\models\GroupUser[] */
/** @var $totalMembersCount int */
?>
<div class="card card-group-members">
    <div class="card-body">
        <h5 class=""><?= Yii::t('youdate', 'Members') ?> &mdash;
            <span class="text-muted">
                <?= Html::a($totalMembersCount, ['group/view', 'alias' => $group->alias, 'subPage' => GroupController::PAGE_MEMBERS]) ?>
            </span>
        </h5>
        <div class="members row mt-4">
            <?php foreach ($randomMembers as $randomMember): ?>
                <div class="col-2 col-sm-4 col-md-3 my-2">
                    <a href="<?= Url::to(['profile/view', 'username' => $randomMember->user->username]) ?>">
                    <span class="avatar avatar-md"
                          style="background-image: url('<?= $randomMember->userProfile->getAvatarUrl(96, 96) ?>')">
                    </span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($randomMembers) == 0): ?>
            <div class="text-muted text-center py-2">
                <?= Yii::t('youdate', 'No members yet') ?>
            </div>
        <?php endif; ?>
    </div>
</div>
