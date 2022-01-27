<?php

namespace app\modules\admin\components;

use app\helpers\Html;
use app\helpers\Url;
use app\models\Group;
use app\models\User;
use app\modules\admin\events\SearchEvent;
use app\modules\admin\widgets\AdminQuickSearchResults;
use app\traits\managers\GroupManagerTrait;
use app\traits\managers\UserManagerTrait;
use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\di\Instance;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\components
 */
class Search extends Component
{
    use UserManagerTrait, GroupManagerTrait;

    const EVENT_GET_RESULTS = 'getResults';
    const EVENT_GET_PROVIDERS = 'getProviders';

    /**
     * @var int
     */
    public $pageSize = 20;
    /**
     * @var int
     */
    public $quickSearchPageSize = 5;
    /**
     * @var User
     */
    public $user;

    public function init()
    {
        parent::init();
        Instance::ensure($this->user, User::class);
    }

    /**
     * @param $searchQuery
     * @return array
     * @throws \Exception
     */
    public function getSearchProviders($searchQuery)
    {
        $searchProviders = [];

        if ($this->user->hasPermission(Permission::USERS)) {
            $searchProviders['users'] = [
                'title' => Yii::t('app', 'Users'),
                'dataProvider' => new ActiveDataProvider([
                    'query' => $this->getUsersQuery($searchQuery),
                    'pagination' => [
                        'pageSize' => $this->pageSize,
                    ]
                ]),
            ];
        }

        if ($this->user->hasPermission(Permission::GROUPS)) {
            $searchProviders['groups'] = [
                'title' => Yii::t('app', 'Groups'),
                'dataProvider' => new ActiveDataProvider([
                    'query' => $this->getGroupsQuery($searchQuery),
                    'pagination' => [
                        'pageSize' => $this->pageSize,
                    ]
                ]),
            ];
        }

        $event = new SearchEvent();
        $event->searchQuery = $searchQuery;
        $event->searchProviders = $searchProviders;
        $this->trigger(self::EVENT_GET_PROVIDERS, $event);

        return $event->searchProviders;
    }

    /**
     * @param $searchQuery
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getResults($searchQuery)
    {
        $results = [];

        $results['general'] = AdminQuickSearchResults::widget([
            'fallback' => Html::a(
                Yii::t('app', 'Search for {0}', Html::tag('strong', Html::encode($searchQuery))),
                Url::to(['search/index', 'q' => $searchQuery])
            )
        ]);

        if ($this->user->hasPermission(Permission::USERS)) {
            $users = $this->getUsersQuery($searchQuery)->limit($this->quickSearchPageSize)->all();
            if (count($users)) {
                $results['users'] = AdminQuickSearchResults::widget([
                    'title' => Yii::t('app', 'Users'),
                    'results' => array_map(function(User $user) {
                        return [
                            'url' => Url::to(['user/info', 'id' => $user->id]),
                            'text' => $user->profile->getDisplayName(),
                            'image' => $user->profile->getAvatarUrl(56, 56),
                        ];
                    }, $users)
                ]);
            }
        }

        if ($this->user->hasPermission(Permission::GROUPS)) {
            $groups = $this->getGroupsQuery($searchQuery)->limit($this->quickSearchPageSize)->all();
            if (count($groups)) {
                $results['groups'] = AdminQuickSearchResults::widget([
                    'title' => Yii::t('app', 'Groups'),
                    'results' => array_map(function(Group $group) {
                        return [
                            'url' => Url::to(['group/update', 'id' => $group->id]),
                            'text' => $group->getDisplayTitle(),
                            'image' => $group->getPhotoThumbnail(56, 56),
                        ];
                    }, $groups)
                ]);
            }
        }

        $event = new SearchEvent();
        $event->searchQuery = $searchQuery;
        $event->searchResults = $results;
        $this->trigger(self::EVENT_GET_RESULTS, $event);

        return implode('', $event->searchResults);
    }

    /**
     * @param $q
     * @return \app\models\query\UserQuery|\yii\db\ActiveQuery
     * @throws \Exception
     */
    protected function getUsersQuery($q)
    {
        return $this->userManager
            ->getQuery(['includeBanned' => true])
            ->andFilterWhere(['or',
                ['like', 'user.email', $q],
                ['like', 'user.username', $q],
                ['like', 'profile.name', $q],
            ]);
    }

    /**
     * @param $q
     * @return \app\models\query\UserQuery|\yii\db\ActiveQuery
     * @throws \Exception
     */
    protected function getGroupsQuery($q)
    {
        return $this->groupManager
            ->getQuery()
            ->andFilterWhere(['or',
                ['like', 'group.title', $q],
                ['like', 'group.alias', $q],
            ]);
    }
}
