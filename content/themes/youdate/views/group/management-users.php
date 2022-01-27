<?php

use app\models\GroupUser;
use app\helpers\Html;
use app\helpers\Url;
use youdate\helpers\Icon;
use youdate\widgets\ActionColumn;
use youdate\widgets\GroupHeader;
use youdate\widgets\GridView;

/* @var $this \app\base\View */
/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchQuery string */
/* @var $status string */

$this->title = Yii::t('youdate', 'Manage users');
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-group-management-users';
?>
<?= GroupHeader::widget([
    'group' => $group,
    'groupUser' => $groupUser,
    'user' => $user,
    'canManage' => true,
    'showCover' => false,
    'showBackButton' => true,
]) ?>
<div class="page-content">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <?= $this->render('_management_navigation', ['group' => $group]) ?>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('youdate', 'Manage users') ?></h3>
                    <div class="card-options">
                        <div class="btn-group mr-2" role="group" aria-label="Basic example">
                            <?= Html::a(Yii::t('youdate', 'All'),
                                ['management-users', 'alias' => $group->alias],
                                ['class' => 'btn btn-sm btn-' . ($status == null ? 'primary' : 'secondary')]
                            ) ?>
                            <?= Html::a(Yii::t('youdate', 'Requests'),
                                ['management-users', 'alias' => $group->alias, 'status' => GroupUser::STATUS_UNDER_MODERATION],
                                ['class' => 'btn btn-sm btn-' . ($status == GroupUser::STATUS_UNDER_MODERATION ? 'primary' : 'secondary')]
                            ) ?>
                            <?= Html::a(Yii::t('youdate', 'Banned'),
                                ['management-users', 'alias' => $group->alias, 'status' => GroupUser::STATUS_BANNED],
                                ['class' => 'btn btn-sm btn-' . ($status == GroupUser::STATUS_BANNED ? 'primary' : 'secondary')]
                            ) ?>
                        </div>
                        <?= Html::beginForm(['management-users', 'alias' => $group->alias], 'get') ?>
                            <div class="input-group">
                                <?php if ($status): ?>
                                    <?= Html::hiddenInput('status', $status) ?>
                                <?php endif; ?>
                                <input type="text" class="form-control form-control-sm rounded"
                                       autocomplete="off"
                                       placeholder="<?= Yii::t('youdate', 'Search') ?>" name="q" value="<?= Html::encode($searchQuery) ?>">
                                <span class="input-group-btn ml-2">
                                    <button class="btn btn-sm btn-secondary" type="submit">
                                        <?= Icon::fe('search') ?>
                                    </button>
                                </span>
                            </div>
                        <?= Html::endForm() ?>
                    </div>
                </div>
                <?php \yii\widgets\Pjax::begin(['id' => 'pjax-group-users']) ?>
                <?= $this->render('/_alert', ['cssClass' => 'm-3']) ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'showHeader' => false,
                    'layout' => '{summary} <div class="table-responsive">{items}</div> {pager}',
                    'options' => ['tag' => false],
                    'summaryOptions' => ['class' => 'summary py-2 px-5 text-muted border-bottom'],
                    'tableOptions' => ['class' => 'table table-outline table-vcenter text-nowrap card-table group-users-grid-view'],
                    'pager' => ['options' => ['class' => 'pagination m-auto p-4 clearfix']],
                    'columns' => [
                        [
                            'attribute' => 'user_id',
                            'format' => 'raw',
                            'contentOptions' => ['width' => 70],
                            'header' => false,
                            'label' => false,
                            'value' => function (GroupUser $groupUser) {
                                $isOnline = $groupUser->user->isOnline;
                                $profile = $groupUser->user->profile;
                                $photoUrl = $profile->getAvatarUrl(64, 64);
                                $onlineStatusHtml = Html::tag('span', '', ['class' => 'avatar-status bg-' . ($isOnline ? 'green' : 'gray')]);

                                return Html::a($onlineStatusHtml, ['profile/view', 'username' => $groupUser->user->username], [
                                    'class' => 'avatar d-block',
                                    'data-pjax' => 0,
                                    'title' => Html::encode($profile->getDisplayName()),
                                    'style' => "background-image: url($photoUrl)",
                                ]);
                            }
                        ],
                        [
                            'attribute' => 'user_id',
                            'format' => 'raw',
                            'value' => function (GroupUser $groupUser) {
                                $html =
                                    Html::tag('div', Html::encode($groupUser->user->profile->getDisplayName()), ['class' => 'profile-name']) .
                                    Html::tag('div', Yii::t('youdate', 'Joined: {0}',
                                        Yii::$app->formatter->asDatetime($groupUser->created_at)), ['class' => 'text-muted small']
                                    );
                                return Html::a($html,
                                    ['profile/view', 'username' => $groupUser->user->username],
                                    ['data-pjax' => 0, 'class' => 'profile-link']
                                );
                            }
                        ],
                        [
                            'class' => ActionColumn::class,
                            'wrapperTag' => 'div',
                            'wrapperOptions' => ['class' => 'd-flex justify-content-end align-items-center'],
                            'template' => '{approve} {decline} {admin} {ban}',
                            'buttons' => [
                                'approve' => function ($url, GroupUser $groupUser, $key) use ($group) {
                                    $url = Url::to(['approve', 'alias' => $group->alias, 'groupUserId' => $groupUser->id]);
                                    if ($groupUser->status === GroupUser::STATUS_UNDER_MODERATION) {
                                        return Html::a(Icon::fe('user-check'), $url, [
                                            'rel' => 'tooltip',
                                            'title' => Yii::t('youdate', 'Approve'),
                                            'data-method' => 'post',
                                            'data-pjax' => 0,
                                            'class' => 'ml-2 btn btn-sm btn-success',
                                        ]);
                                    }

                                    return null;
                                },
                                'decline' => function ($url, GroupUser $groupUser, $key) use ($group) {
                                    $url = Url::to(['decline', 'alias' => $group->alias, 'groupUserId' => $groupUser->id]);
                                    if ($groupUser->status === GroupUser::STATUS_UNDER_MODERATION) {
                                        return Html::a(Icon::fe('user-x'), $url, [
                                            'rel' => 'tooltip',
                                            'title' => Yii::t('youdate', 'Decline'),
                                            'data-method' => 'post',
                                            'data-pjax' => 0,
                                            'class' => 'ml-2 btn btn-sm btn-danger',
                                        ]);
                                    }

                                    return null;
                                },
                                'admin' => function ($url, GroupUser $groupUser, $key) use ($group) {
                                    $url = Url::to(['toggle-admin', 'alias' => $group->alias, 'groupUserId' => $groupUser->id]);
                                    $isAdmin = $groupUser->role === GroupUser::ROLE_ADMIN;
                                    return Html::a(Icon::fe('sliders'), $url, [
                                        'rel' => 'tooltip',
                                        'data-method' => 'post',
                                        'data-pjax' => 0,
                                        'class' => 'ml-2 btn btn-sm btn-' . ($isAdmin ? 'primary' : 'secondary'),
                                        'title' => $isAdmin ?
                                            Yii::t('youdate', 'Remove admin') :
                                            Yii::t('youdate', 'Add admin'),
                                    ]);
                                },
                                'ban' => function ($url, GroupUser $groupUser, $key) use ($group) {
                                    $url = Url::to(['toggle-ban', 'alias' => $group->alias, 'groupUserId' => $groupUser->id]);
                                    $isBanned = $groupUser->status === GroupUser::STATUS_BANNED;
                                    $icon = Icon::fe($isBanned ? 'lock' : 'user');
                                    return Html::a($icon, $url, [
                                        'rel' => 'tooltip',
                                        'data-method' => 'post',
                                        'data-pjax' => 0,
                                        'class' => 'ml-2 btn btn-sm btn-' . ($isBanned ? 'danger' : 'secondary'),
                                        'title' => $isBanned ?
                                            Yii::t('youdate', 'Remove ban') :
                                            Yii::t('youdate', 'Ban user'),
                                    ]);
                                },
                            ]
                        ]
                    ]
                ]) ?>
                <?php \yii\widgets\Pjax::end() ?>
            </div>
        </div>
    </div>
</div>
