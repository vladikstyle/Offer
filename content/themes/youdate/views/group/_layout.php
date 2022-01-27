<?php

use app\controllers\GroupController;
use youdate\widgets\GroupHeader;
use youdate\widgets\GroupMembers;
use youdate\widgets\EmptyState;

/* @var $this \app\base\View */
/* @var $content string */
/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */
/* @var $canView bool */
/* @var $canManage bool */
/* @var $subPage string */

$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-group-view';
$showMembersWidget = isset($showMembersWidget) ? $showMembersWidget : true;
?>
<?= GroupHeader::widget([
    'group' => $group,
    'groupUser' => $groupUser,
    'user' => $user,
    'canView' => $canView,
    'canManage' => $canManage,
]) ?>
<?php if (!$canView): ?>
    <div class="card">
        <div class="card-block">
            <?= EmptyState::widget([
                'icon' => 'fe fe-grid',
                'title' => Yii::t('youdate', 'No access'),
                'subTitle' => Yii::t('youdate', 'Sorry, you don\'t have access to this group'),
            ]) ?>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12 col-md-7 col-lg-3">
            <?= \youdate\widgets\Sidebar::widget([
                'header' => false,
                'options' => [
                    'class' => 'sidebar-menu list-group list-group-transparent mb-0',
                ],
                'items' => [
                    [
                        'label' => Yii::t('youdate', 'Feed'),
                        'url' => ['group/view', 'alias' => $group->alias],
                        'icon' => 'home',
                        'active' => $subPage == GroupController::PAGE_FEED,
                    ],
                    [
                        'label' => Yii::t('youdate', 'Members'),
                        'url' => ['group/view', 'alias' => $group->alias, 'subPage' => GroupController::PAGE_MEMBERS],
                        'icon' => 'users',
                        'active' => $subPage == GroupController::PAGE_MEMBERS,
                    ],
                    [
                        'label' => Yii::t('youdate', 'Info'),
                        'url' => ['group/view', 'alias' => $group->alias, 'subPage' => GroupController::PAGE_INFO],
                        'icon' => 'info',
                        'active' => $subPage == GroupController::PAGE_INFO,
                    ],
                ],
            ]) ?>
        </div>
        <div class="col-12 col-md-7 col-lg-6">
            <?= $content ?>
        </div>
        <div class="col-12 col-md-5 col-lg-3">
            <?php if ($showMembersWidget): ?>
                <?= GroupMembers::widget(['group' => $group, 'maxMembers' => 12]) ?>
            <?php endif; ?>
            <?php if (isset($this->params['user.ads.hide']) && !$this->params['user.ads.hide'] || Yii::$app->user->isGuest): ?>
                <div class="mb-5"><?= $this->themeSetting('adsSidebar') ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif;
