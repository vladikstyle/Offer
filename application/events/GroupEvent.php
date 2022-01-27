<?php

namespace app\events;

use app\base\Event;
use app\models\Group;
use app\models\GroupUser;
use app\models\User;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\events
 * @property Group $group
 * @property GroupUser $groupUser
 * @property User $user
 */
class GroupEvent extends Event
{
    /**
     * @var Group
     */
    public $group;
    /**
     * @var GroupUser
     */
    public $groupUser;
    /**
     * @var User
     */
    public $user;
}
