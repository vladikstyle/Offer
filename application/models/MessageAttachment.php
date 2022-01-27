<?php

namespace app\models;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property string $message_id
 * @property string $type
 * @property string $data
 *
 * @property Message $message
 */
class MessageAttachment extends \app\base\ActiveRecord
{
    const TYPE_IMAGE = 'image';

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%message_attachment}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['message_id', 'type'], 'required'],
            [['message_id'], 'integer'],
            [['data'], 'string'],
            [['type'], 'string', 'max' => 32],
            [['message_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Message::class,
                'targetAttribute' => ['message_id' => 'id']
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => Yii::t('app', 'Message ID'),
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(Message::class, ['id' => 'message_id']);
    }
}
