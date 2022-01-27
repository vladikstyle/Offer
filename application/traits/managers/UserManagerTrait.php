<?php

namespace app\traits\managers;

use app\managers\UserManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property UserManager $userManager
 */
trait UserManagerTrait
{
    /**
     * @var string
     */
    protected $userManagerComponent = 'userManager';
    /**
     * @var UserManager
     */
    protected $userManagerCached;

    /**
     * @return object|null|UserManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getUserManager()
    {
        if (!isset($this->userManagerCached)) {
            $this->userManagerCached = Yii::$app->get($this->userManagerComponent);
        }

        return $this->userManagerCached;
    }
}
