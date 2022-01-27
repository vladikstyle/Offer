<?php

namespace app\models\query;

use app\models\Message;
use yii\db\Expression;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class MessageQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return MessageQuery
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }

    public function init()
    {
        parent::init();
        $this->alias('m');
        $this->select('m.*');
    }

    /**
     * @inheritdoc
     * @return Message[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Message|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $fromUserId
     * @param $toUserId
     * @return $this
     */
    public function between($fromUserId, $toUserId)
    {
        return $this->andWhere(['or',
            ['from_user_id' => $fromUserId, 'to_user_id' => $toUserId, 'is_deleted_by_receiver' => false],
            ['from_user_id' => $toUserId, 'to_user_id' => $fromUserId, 'is_deleted_by_sender' => false],
        ]);
    }

    /**
     * @param int $targetUserId
     * @return $this
     */
    public function whereTargetUser($targetUserId)
    {
        return $this->andWhere(['or',
            ['from_user_id' => $targetUserId],
            ['to_user_id' => $targetUserId],
        ]);
    }

    /**
     * @param int $senderId
     * @return $this
     */
    public function whereSender($senderId)
    {
        return $this->andWhere(['from_user_id' => $senderId]);
    }

    /**
     * @param int $receiverId
     * @return $this
     */
    public function whereReceiver($receiverId)
    {
        return $this->andWhere(['to_user_id' => $receiverId]);
    }

    /**
     * @param $toUserId
     * @return $this
     */
    public function withUserData($toUserId)
    {
        return $this
            ->addSelect([
                new Expression('IF ([[from_user_id]] = :userId, [[to_user_id]], [[from_user_id]]) as contact_id', [
                    ':userId' => $toUserId,
                ]),
                'senderProfile.photo_id',
                'receiverProfile.photo_id',
            ])
            ->joinWith(['senderProfile', 'receiverProfile']);
    }

    /**
     * @param $toUserId
     * @return $this
     */
    public function withType($toUserId)
    {
        return $this->addSelect(
            new Expression('IF ([[from_user_id]] = :userId, :sent, :inbox) as type', [
                ':userId' => $toUserId,
                ':inbox' => Message::TYPE_INBOX,
                ':sent' => Message::TYPE_SENT,
            ])
        );
    }

    /**
     * @return $this
     */
    public function onlyNew()
    {
        return $this->andWhere(['is_new' => 1]);
    }
}
