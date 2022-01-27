<?php

namespace youdate\widgets;

use app\models\Group;
use app\models\GroupUser;
use app\models\User;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class GroupHeader extends Widget
{
    /**
     * @var User
     */
    public $user;
    /**
     * @var Group
     */
    public $group;
    /**
     * @var GroupUser|null
     */
    public $groupUser;
    /**
     * @var bool
     */
    public $canView;
    /**
     * @var bool
     */
    public $canManage;
    /**
     * @var bool
     */
    public $showCover = true;
    /**
     * @var bool
     */
    public $showBackButton = false;

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('groups/header', [
            'group' => $this->group,
            'groupUser' => $this->groupUser,
            'user' => $this->user,
            'canView' => $this->canView,
            'canManage' => $this->canManage,
            'showCover' => $this->showCover,
            'showBackButton' => $this->showBackButton,
        ]);
    }
}
