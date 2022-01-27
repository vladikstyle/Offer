<?php

namespace app\traits\managers;

use app\managers\NotificationManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property NotificationManager $notificationManager
 */
trait NotificationManagerTrait
{
    /**
     * @var string
     */
    protected $notificationManagerComponent = 'notificationManager';
    /**
     * @var NotificationManager
     */
    protected $notificationManagerCached;

    /**
     * @return object|null|NotificationManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getNotificationManager()
    {
        if (!isset($this->notificationManagerCached)) {
            $this->notificationManagerCached = Yii::$app->get($this->notificationManagerComponent);
        }

        return $this->notificationManagerCached;
    }
}
