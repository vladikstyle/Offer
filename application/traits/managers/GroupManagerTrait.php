<?php

namespace app\traits\managers;

use app\managers\GroupManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property GroupManager $groupManager
 */
trait GroupManagerTrait
{
    /**
     * @var string
     */
    protected $groupManagerComponent = 'groupManager';
    /**
     * @var groupManager
     */
    protected $groupManagerCached;

    /**
     * @return object|null|GroupManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getGroupManager()
    {
        if (!isset($this->groupManagerCached)) {
            $this->groupManagerCached = Yii::$app->get($this->groupManagerComponent);
        }

        return $this->groupManagerCached;
    }
}
