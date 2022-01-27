<?php

namespace app\jobs;

use app\models\Message;
use app\models\User;
use app\settings\UserSettings;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\jobs
 */
class CheckNewMessages extends BaseObject implements \yii\queue\JobInterface
{
    const NEW_MESSAGES_EMAIL_AT = 'newMessagesEmailAt';
    const LAST_NEW_MESSAGE_ID = 'lastNewMessageId';

    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $secondsSinceLastCheck = 60;

    /**
     * @param Queue $queue
     * @return mixed|void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        $user = User::findOne(['id' => $this->userId]);
        if ($user == null) {
            return;
        }

        if (isset($user->profile->language_id)) {
            Yii::$app->language = $user->profile->language_id;
        }

        $userSettings = UserSettings::forUser($user->id);
        $newMessagesEmailAt = $userSettings->getUserSetting(self::NEW_MESSAGES_EMAIL_AT, null);
        $savedLastMessageId = $userSettings->getUserSetting(self::LAST_NEW_MESSAGE_ID, null);

        // user doesnt need these mails
        if ($userSettings->getUserSetting('receiveEmailOnNewMessages') == false) {
            return;
        }

        // dont spam
        if ($newMessagesEmailAt !== null && time() - $newMessagesEmailAt < $this->secondsSinceLastCheck) {
            return;
        }

        // only newest messages
        $query = Message::find()->whereReceiver($user->id)->onlyNew();
        if ($savedLastMessageId !== null) {
            $query->andWhere(['>', 'id', $savedLastMessageId]);
        }
        $messages = $query->all();
        if (count($messages) == 0) {
            return;
        }

        $lastMessageId = Message::find()->whereTargetUser($user->id)->onlyNew()->max('id');
        $userSettings->setUserSetting(self::NEW_MESSAGES_EMAIL_AT, time());
        $userSettings->setUserSetting(self::LAST_NEW_MESSAGE_ID, $lastMessageId);

        Yii::$app->appMailer->sendMessage(
            $user->email,
            Yii::t('app', 'You have new messages'),
            'new-messages', [
                'user' => $user,
                'messages' => $messages,
            ]
        );
    }
}
