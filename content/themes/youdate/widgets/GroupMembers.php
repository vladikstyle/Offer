<?php

namespace youdate\widgets;

use app\models\Group;
use Yii;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class GroupMembers extends Widget
{
    /**
     * @var Group
     */
    public $group;
    /**
     * @var int
     */
    public $maxMembers = 6;

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $randomMembers = Yii::$app->groupManager->getMemberQuery()
            ->joinWith(['user', 'userProfile'])
            ->whereGroup($this->group)
            ->withoutBanned()
            ->limit($this->maxMembers)
            ->orderBy('rand()')
            ->all();

        return $this->render('groups/members', [
            'group' => $this->group,
            'randomMembers' => $randomMembers,
            'totalMembersCount' => $this->group->getGroupUsersCount(),
        ]);
    }
}
