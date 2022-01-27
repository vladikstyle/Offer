<?php

namespace app\jobs;

use app\notifications\BaseNotification;
use app\traits\managers\NotificationManagerTrait;
use yii\base\BaseObject;
use yii\db\ActiveQuery;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\jobs
 */
class SendBulkNotification extends BaseObject implements \yii\queue\JobInterface
{
    use NotificationManagerTrait;

    /**
     * @var BaseNotification|BaseNotification[]
     */
    public $notification;
    /**
     * @var ActiveQuery
     */
    public $query;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $this->notificationManager->sendBulk($this->notification, $this->query);
    }
}
