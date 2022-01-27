<?php

namespace app\traits\managers;

use app\managers\GiftManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property GiftManager $giftManager
 */
trait GiftManagerTrait
{
    /**
     * @var string
     */
    protected $giftManagerComponent = 'giftManager';
    /**
     * @var GiftManager
     */
    protected $giftManagerCached;

    /**
     * @return object|null|GiftManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getGiftManager()
    {
        if (!isset($this->giftManagerCached)) {
            $this->giftManagerCached = Yii::$app->get($this->giftManagerComponent);
        }

        return $this->giftManagerCached;
    }
}
