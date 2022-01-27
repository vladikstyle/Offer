<?php

namespace app\managers;

use app\base\Event;
use app\events\MessageAttachmentEvent;
use app\events\MessageEvent;
use app\jobs\CheckNewMessages;
use app\models\Conversation;
use app\models\Message;
use app\models\MessageAttachment;
use app\models\query\ConversationQuery;
use app\models\query\MessageQuery;
use app\traits\CacheTrait;
use app\traits\CurrentUserTrait;
use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\web\Application;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\managers
 */
class MessageManager extends Component
{
    use CurrentUserTrait, CacheTrait;

    const EVENT_BEFORE_MESSAGE_CREATE = 'onBeforeMessageCreate';
    const EVENT_AFTER_MESSAGE_CREATE = 'onAfterMessageCreate';
    const EVENT_BEFORE_ATTACHMENT_CREATE = 'onBeforeAttachmentCreate';
    const EVENT_AFTER_ATTACHMENT_CREATE = 'onAfterAttachmentCreate';
    const CACHE_KEY_NEW_CONVERSATIONS = 'newConversations';
    const CACHE_KEY_NEW_MESSAGES_COUNTERS = 'messagesNewCounters';

    /**
     * @var int
     */
    public $delayBeforeNotification = 30;

    public function init()
    {
        parent::init();

        $resetCounters = function (\yii\base\Event $event) {
            /** @var Message $message */
            $message = $event->sender;
            $this->resetCountersCache($message->to_user_id);
        };

        if (Yii::$app instanceof Application && !Yii::$app->user->isGuest) {
            Event::on(Message::class, Message::EVENT_AFTER_INSERT, $resetCounters);
            Event::on(Message::class, Message::EVENT_AFTER_UPDATE, $resetCounters);
            Event::on(Message::class, Message::EVENT_AFTER_DELETE, $resetCounters);
        }
    }

    /**
     * @param $userId
     * @param string|null $searchQuery
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getConversations($userId, $searchQuery = null)
    {
        $query = $this->getConversationsQuery($userId)
            ->with('lastMessage')
            ->withContact()
            ->indexBy('contact_id')
            ->orderBy(['last_message_id' => SORT_DESC])
            ->limit(50);

        if ($query !== null) {
            $query
                ->joinWith(['senderProfile', 'receiverProfile'])
                ->andFilterWhere(['or',
                    ['like', 'senderProfile.name', $searchQuery],
                    ['like', 'receiverProfile.name', $searchQuery],
                ]);
        }

        $conversations = $query->all();
        $premium = [];
        $free = [];
        foreach ($conversations as $contactId => $conversation) {
            $fields = $conversation->fields();
            $contact = call_user_func($fields['contact'], $conversation);
            if ($contact['premium'] === true) {
                $premium[$contactId] = $conversation;
            } else {
                $free[$contactId] = $conversation;
            }
        }

        return array_merge($premium, $free);
    }

    /**
     * @param $fromUserId
     * @param $toUserId
     * @return Message[]|array
     */
    public function getMessages($fromUserId, $toUserId)
    {
        $query = Message::find()
            ->joinWith('attachments')
            ->between($fromUserId, $toUserId)
            ->withUserData($toUserId)
            ->withType($toUserId)
            ->limit(100)
            ->orderBy('id desc')
            ->indexBy('id');

        return (array) $query->all();
    }

    /**
     * @param $targetUserId
     * @param $ids
     * @return Message[]|array
     */
    public function getMessagesForUser($targetUserId, $ids = [])
    {
        $query = Message::find()->whereTargetUser($targetUserId);

        if ($ids !== false && count($ids)) {
            $query->andWhere(['in', 'id', $ids]);
        }

        return $query->all();
    }

    /**
     * @param $userId
     * @param $contactId
     * @param $limit
     * @param bool $history
     * @param null $key
     * @return ActiveDataProvider
     */
    public function getMessagesProvider($userId, $contactId, $limit, $history = true, $key = null)
    {
        $query = $this->getMessagesQuery($userId, $contactId);

        if (null !== $key) {
            $query->andWhere([$history ? '<' : '>', 'id', $key]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit
            ]
        ]);
    }

    /**
     * @param $userId
     * @param bool $history
     * @param null $key
     * @return ActiveDataProvider
     */
    public function getConversationsProvider($userId, $history = true, $key = null)
    {
        $query = Conversation::find()->forUser($userId);
        if (null !== $key) {
            $query->andHaving([$history ? '<' : '>', 'last_message_id', $key]);
        }

        $query->indexBy('last_message_id');

        return new ActiveDataProvider([
            'query' => $query,
            'key' => 'last_message_id',
        ]);
    }

    /**
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getNewMessagesCounters($userId)
    {
        $cacheKey = self::CACHE_KEY_NEW_MESSAGES_COUNTERS . '_' . $userId;
        $counters = $this->cache->get($cacheKey);

        if ($counters === false) {
            $counters = Message::find()
                ->onlyNew()
                ->select([
                    'from_user_id as contact_id',
                    'sum(is_new) AS new_messages_count',
                ])
                ->andWhere(['to_user_id' => $userId, 'is_deleted_by_receiver' => 0])
                ->groupBy('from_user_id')
                ->indexBy('contact_id')
                ->asArray()
                ->all();

            $this->cache->set($cacheKey, $counters, 3600);
        }

        return $counters;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getNewMessagesCount($userId)
    {
        $cacheKey = self::CACHE_KEY_NEW_CONVERSATIONS . '_' . $userId;
        $count = $this->cache->get($cacheKey);

        if ($count === false) {
            $count = (int) Message::find()
                ->onlyNew()
                ->addSelect([
                    'from_user_id as contact_id',
                    'sum(is_new) AS `new_messages_count`',
                ])
                ->andWhere(['to_user_id' => $userId, 'is_deleted_by_receiver' => 0])
                ->groupBy('from_user_id')
                ->sum('is_new');

            $this->cache->set($cacheKey, $count, 3600);
        }

        return $count;
    }

    /**
     * @param $fromId
     * @param $contactId
     * @param $text
     * @return Message
     */
    public function createMessage($fromId, $contactId, $text)
    {
        $message = new Message(['scenario' => Message::SCENARIO_CREATE]);
        $message->from_user_id = $fromId;
        $message->to_user_id = $contactId;
        $message->text = $text;

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_BEFORE_MESSAGE_CREATE, $event);

        if ($event->isValid) {
            $message->save();
            $this->trigger(self::EVENT_AFTER_MESSAGE_CREATE, $event);
            $job = new CheckNewMessages();
            $job->userId = $contactId;
            Yii::$app->queue->delay($this->delayBeforeNotification)->push($job);
        }

        $this->resetCountersCache($contactId);

        return $message;
    }

    /**
     * @param Message $message
     * @param $type
     * @param $data
     * @return Message
     */
    public function addAttachment($message, $type, $data)
    {
        $attachment = new MessageAttachment();
        $attachment->message_id = $message->id;
        $attachment->type = $type;
        $attachment->data = $data;

        $attachmentEvent = new MessageAttachmentEvent();
        $attachmentEvent->message = $message;
        $attachmentEvent->attachment = $attachment;
        $this->trigger(self::EVENT_BEFORE_ATTACHMENT_CREATE, $attachmentEvent);

        if ($attachmentEvent->isValid) {
            $attachment->save();
            $this->trigger(self::EVENT_AFTER_ATTACHMENT_CREATE, $attachmentEvent);
        }

        return $message;
    }

    /**
     * @param $userId
     * @param $ids
     * @return int
     */
    public function deleteMessages($userId, $ids)
    {
        $messages = $this->getMessagesForUser($userId, $ids);
        $count = 0;

        foreach ($messages as $message) {
            if ($message->from_user_id == Yii::$app->user->id) {
                $message->is_deleted_by_sender = 1;
            } else {
                $message->is_deleted_by_receiver = 1;
            }
            if ($message->save()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $fromUserId
     * @param $toUserId
     * @return MessageQuery
     */
    protected function getMessagesQuery($fromUserId, $toUserId)
    {
        return Message::find()
            ->orderBy(['id' => SORT_DESC])
            ->between($fromUserId, $toUserId);
    }

    /**
     * @param $userId
     * @return ConversationQuery
     */
    protected function getConversationsQuery($userId)
    {
        return Conversation::find()
            ->forUser($userId);
    }

    /**
     * @param $userId
     * @param $contactId
     * @return array the number of rows updated
     */
    public function deleteConversation($userId, $contactId)
    {
        $count = Conversation::updateAll([
            'is_deleted_by_sender' => new Expression('IF([[from_user_id]] = :userId, TRUE, is_deleted_by_sender)'),
            'is_deleted_by_receiver' => new Expression('IF([[to_user_id]] = :userId, TRUE, is_deleted_by_receiver)')
        ], ['or',
            ['to_user_id' => new Expression(':userId'), 'from_user_id' => $contactId, 'is_deleted_by_receiver' => false],
            ['from_user_id' => new Expression(':userId'), 'to_user_id' => $contactId, 'is_deleted_by_sender' => false],
        ], [
            'userId' => $userId
        ]);

        return compact('count');
    }


    /**
     * @param $userId
     * @param $contactId
     * @return array the number of rows updated
     */
    public function readConversation($userId, $contactId)
    {
        $mutexName = 'readConversation_' . $userId . '_' . $contactId;
        $count = 0;

        if (Yii::$app->mutex->acquire($mutexName)) {
            $count = Conversation::updateAll(['is_new' => false], [
                'to_user_id' => $userId,
                'from_user_id' => $contactId,
                'is_new' => true
            ]);

            $this->resetCountersCache($userId);

            Yii::$app->mutex->release($mutexName);
        }

        return ['count' => $count];
    }

    /**
     * @param $userId
     * @param $contactId
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function unreadConversation($userId, $contactId)
    {
        /** @var Message $message */
        $message = Message::find()
            ->where(['from_user_id' => $contactId, 'to_user_id' => $userId, 'is_deleted_by_receiver' => false])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();

        $count = 0;
        if ($message) {
            $message->is_new = 1;
            $count = intval($message->update());
        }

        return compact('count');
    }

    /**
     * @param Message $message
     * @return array|bool|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getMessageAttachmentsData(Message $message)
    {
        $data = [];
        foreach ($message->attachments as $attachment) {
            switch ($attachment->type) {
                case MessageAttachment::TYPE_IMAGE:
                    $thumbnailsOptions = [
                        'id' => $attachment->id,
                        'w' => 600, 'h' => 300, 'sharp' => 1,
                    ];
                    $thumbnailsUrl = null;
                    if (Yii::$app->glide->cachedFileExists($attachment->data, $thumbnailsOptions)) {
                        $thumbnailsUrl = Yii::$app->glide->getCachedImage($attachment->data, $thumbnailsOptions);
                    }
                    $thumbnailsOptions = array_merge(['messages/image-thumbnail'], $thumbnailsOptions);
                    if ($thumbnailsUrl === null) {
                        $thumbnailsUrl = Yii::$app->glide->createSignedUrl($thumbnailsOptions, true);
                    }
                    $data[] = [
                        'id' => $attachment->id,
                        'type' => $attachment->type,
                        'url' => Yii::$app->photoStorage->getUrl($attachment->data),
                        'thumbnail' => $thumbnailsUrl,
                    ];
                    break;
            }
        }

        return $data;
    }

    /**
     * @param $id
     * @param $contactId
     * @return array|null|\yii\db\ActiveRecord|MessageAttachment
     */
    public function getMessageAttachment($id, $contactId)
    {
        return MessageAttachment::find()
            ->where(['message_attachment.id' => $id])
            ->joinWith('message')
            ->andWhere(['or',
                'm.from_user_id' => $contactId,
                'm.to_user_id' => $contactId,
            ])
            ->one();
    }

    /**
     * @param $userId
     */
    protected function resetCountersCache($userId)
    {
        $this->cache->delete(self::CACHE_KEY_NEW_CONVERSATIONS . '_' . $userId);
        $this->cache->delete(self::CACHE_KEY_NEW_MESSAGES_COUNTERS . '_' . $userId);
    }
}
