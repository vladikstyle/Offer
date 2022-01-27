<?php

namespace app\models;

use app\models\query\MessageQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property string $id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property string $text
 * @property int $is_new
 * @property int $is_deleted_by_sender
 * @property int $is_deleted_by_receiver
 * @property int $created_at
 *
 * @property User $sender
 * @property Profile $senderProfile
 * @property User $receiver
 * @property Profile $receiverProfile
 * @property MessageAttachment[] $attachments
 */
class Message extends \app\base\ActiveRecord
{
    const TYPE_INBOX = 'inbox';
    const TYPE_SENT = 'sent';

    /**
     * @inheritdoc
     * @return MessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_user_id', 'to_user_id'], 'required'],
            [['from_user_id', 'to_user_id', 'is_new', 'is_deleted_by_sender', 'is_deleted_by_receiver', 'created_at'], 'integer'],
            [['text'], 'string', 'max' => 1000],
            [['created_at'], 'integer'],
            [['from_user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['from_user_id' => 'id']
            ],
            [['to_user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['to_user_id' => 'id']
            ],
            ['from_user_id', 'compare', 'compareAttribute' => 'to_user_id', 'operator' => '!='],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = ['to_user_id', 'text'];

        return $scenarios;
    }

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'type',
            'contact_id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_user_id' => Yii::t('app', 'Sender'),
            'to_user_id' => Yii::t('app', 'Receiver'),
            'text' => Yii::t('app', 'Message'),
            'is_new' => Yii::t('app', 'New'),
            'is_deleted_by_sender' => Yii::t('app', 'Deleted'),
            'is_deleted_by_receiver' => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'from_user_id'])->alias('sender');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSenderProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'from_user_id'])->alias('senderProfile');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id'])->alias('receiver');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'to_user_id'])->alias('receiverProfile');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(MessageAttachment::class, ['message_id' => 'id']);
    }

    /**
     * @return false|string
     */
    public function getCreatedDateTime()
    {
        return gmdate('Y-m-d H:i:s', $this->created_at);
    }

    /**
     * @param $userId
     * @return bool
     */
    public function hasAccess($userId)
    {
        return $this->from_user_id == $userId || $this->to_user_id == $userId;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'contact_id',
            'from_user_id',
            'to_user_id',
            'datetime' => function($model) {
                $dt = new \DateTime('@' . $model->created_at);
                $dt->setTimeZone(new \DateTimeZone(Yii::$app->timeZone));

                return $dt->format('Y-m-d H:i:s');
            },
            'type',
            'text',
            'is_new',
            'user' => function (Message $message) {
                /** @var User $user */
                $user = $message->sender;
                /** @var Profile $profile */
                $profile = $message->senderProfile;
                return [
                    'id' => $user->id,
                    'avatar' => $profile !== null ? $profile->getAvatarUrl(48, 48) : null,
                    'full_name' => $profile !== null ? $profile->getDisplayName() : $user->username,
                    'online' => $user->isOnline,
                    'verified' => $user->profile->is_verified,
                ];
            },
            'attachments' => function (Message $message) {
                return (array) Yii::$app->messageManager->getMessageAttachmentsData($message);
            }
        ];
    }
}
