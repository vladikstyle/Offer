<?php

namespace app\traits\managers;

use app\managers\GuestManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property GuestManager $guestManager
 */
trait GuestManagerTrait
{
    /**
     * @var string
     */
    protected $guestManagerComponent = 'guestManager';
    /**
     * @var GuestManager
     */
    protected $guestManagerCached;

    /**
     * @return object|null|GuestManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getGuestManager()
    {
        if (!isset($this->guestManagerCached)) {
            $this->guestManagerCached = Yii::$app->get($this->guestManagerComponent);
        }

        return $this->guestManagerCached;
    }
}
