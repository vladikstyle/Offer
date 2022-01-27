<?php

namespace app\managers;

use app\models\Notification;
use app\models\User;
use app\notifications\BaseNotification;
use app\notifications\BaseNotificationCategory;
use app\settings\UserSettings;
use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\managers
 */
class NotificationManager extends Component
{
    const EVENT_NOTIFICATIONS_LIST = 'notificationsList';

    /**
     * @var array
     */
    public $notificationClasses = [];
    /**
     * @var BaseNotification[]
     */
    protected $_notifications;
    /**
     * @var BaseNotificationCategory[]
     */
    protected $_categories;

    /**
     * @param $userId
     * @return bool
     */
    public function hasNewNotifications($userId)
    {
        return $this->getQuery()->whereUserId($userId)->onlyNew()->count() > 0;
    }

    /**
     * @param $fromUser
     * @param $toUser
     * @param $class
     * @param null $condition
     * @return bool
     */
    public function isNotificationSent($fromUser, $toUser, $class, $condition = null)
    {
        $query = $this->getQuery()
            ->bySenderId($fromUser->id)
            ->whereUserId($toUser->id)
            ->andWhere(['class' => $class]);

        if ($condition !== null) {
            $query->andWhere($condition);
        }

        return $query->count() > 0;
    }

    /**
     * @param BaseNotification $notification
     * @param $userQuery ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function sendBulk(BaseNotification $notification, $userQuery)
    {
        $processed = [];

        /** @var User $user */
        foreach ($userQuery->each() as $user) {

            if ($notification->isSender($user)) {
                continue;
            }

            if (in_array($user->id, $processed)) {
                continue;
            }

            $sendEmail = UserSettings::forUser($user->id)->getUserSetting($notification->getUserSettingsKey());
            if ($sendEmail == true && $user->tag !== 'fake') {
                Yii::$app->appMailer->sendMessage($user->email, $notification->text(), 'notification', [
                    'notification' => $notification,
                    'receiver' => $user,
                    'sender' => $notification->sender,
                ]);
            }

            $processed[] = $user->id;
        }
    }

    /**
     * @param BaseNotification $notification
     * @param User $user
     * @throws \yii\base\InvalidConfigException
     */
    public function send(BaseNotification $notification, User $user)
    {
        $this->sendBulk($notification, User::find()->where(['user.id' => $user->id]));
    }

    /**
     * @param $userId
     * @return int
     */
    public function markAllAsViewed($userId)
    {
        return Notification::updateAll(['is_viewed' => true], ['user_id' => $userId]);
    }

    /**
     * @return BaseNotificationCategory[]|array
     * @throws \yii\base\InvalidConfigException
     */
    public function getNotificationCategories()
    {
        if ($this->_categories) {
            return $this->_categories;
        }

        $result = [];

        foreach ($this->getNotifications() as $notification) {
            $category = $notification->getCategory();
            if ($category && !array_key_exists($category->id, $result)) {
                $result[$category->id] = $category;
            }
        }

        $this->_categories = array_values($result);

        usort($this->_categories, function ($a, $b) {
            return $a->sortOrder - $b->sortOrder;
        });

        return $this->_categories;
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function getNotificationsProvider($params = [])
    {
        $query = $this->getQuery();
        $query->orderBy('created_at desc');
        $query->whereUserId($params['userId']);

        if (isset($params['filters']) && is_array($params['filters']) && count($params['filters'])) {
            $query->andWhere(['in', 'class', $this->getFilteredClasses($params['filters'])]);
        }

        if (isset($params['onlyNew']) && $params['onlyNew']) {
            $query->onlyNew();
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => ArrayHelper::getValue($params, 'pageSize', 1),
            ],
        ]);
    }

    /**
     * @param $filters
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getFilteredClasses($filters)
    {
        $results = [];

        foreach ($this->getNotifications() as $notification) {
            $id = $notification->getCategory()->id;
            if (in_array($id, $filters)) {
                $results[] = get_class($notification);
            }
        }

        return $results;
    }

    /**
     * @param User $user
     */
    public function deleteNotificationsFrom(User $user)
    {
        Notification::deleteAll(['notification.sender_user_id' => $user->id]);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getUserSettings()
    {
        $settings = [];
        foreach ($this->notificationClasses as $notificationClass) {
            /** @var BaseNotification $notificationClass */
            $instance = $notificationClass::instance();
            $settings[] = [
                'alias' => $instance->getUserSettingsKey(),
                'type' => 'checkbox',
                'label' => $instance->getCategory()->getDescription(),
                'rules' => [['boolean']]
            ];
        }

        $settings[] = [
            'alias' => 'receiveEmailOnNewMessages',
            'type' => 'checkbox',
            'label' => Yii::t('app', 'Receive Notifications for new messages'),
            'rules' => [['boolean']],
        ];

        return $settings;
    }

    /**
     * @return \app\models\query\NotificationQuery
     */
    protected function getQuery()
    {
        return Notification::find();
    }

    /**
     * @return BaseNotification[]
     * @throws \yii\base\InvalidConfigException
     */
    protected function getNotifications()
    {
        if (!isset($this->_notifications)) {
            $this->_notifications = $this->createNotifications($this->notificationClasses);
        }

        return $this->_notifications;
    }

    /**
     * @param $notificationClasses
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function createNotifications($notificationClasses)
    {
        $data = [];
        foreach ($notificationClasses as $notificationClass) {
            $data[] = Yii::createObject($notificationClass);
        }
        return $data;
    }
}
