<?php

namespace app\traits\managers;

use app\managers\MessageManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property MessageManager $messageManager
 */
trait MessageManagerTrait
{
    /**
     * @var string
     */
    protected $messageManagerComponent = 'messageManager';
    /**
     * @var MessageManager
     */
    protected $messageManagerCached;

    /**
     * @return object|null|MessageManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getMessageManager()
    {
        if (!isset($this->messageManagerCached)) {
            $this->messageManagerCached = Yii::$app->get($this->messageManagerComponent);
        }

        return $this->messageManagerCached;
    }
}
