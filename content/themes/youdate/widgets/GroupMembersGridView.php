<?php

namespace youdate\widgets;

use app\helpers\Html;
use app\helpers\Url;
use app\models\Group;
use app\models\GroupUser;
use Yii;
use yii\base\Widget;
use yii\data\ActiveDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class GroupMembersGridView extends Widget
{
    /**
     * @var Group
     */
    public $group;

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $membersDataProvider = new ActiveDataProvider([
            'query' => Yii::$app->groupManager->getMemberQuery()
                ->joinWith(['user', 'userProfile', 'userProfile'])
                ->whereGroup($this->group)
                ->withoutBanned(),
        ]);

        return GridView::widget([
            'dataProvider' => $membersDataProvider,
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
            ]
        ]);
    }
}
