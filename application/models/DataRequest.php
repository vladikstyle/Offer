<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property string $code
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class DataRequest extends \app\base\ActiveRecord
{
    const STATUS_QUEUED = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_DONE = 10;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%data_request}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'code'], 'required'],
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
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
            'user_id' => Yii::t('app', 'User'),
            'status' => Yii::t('app', 'Status'),
            'code' => Yii::t('app', 'Code'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return false|string
     */
    public function getRequestDate()
    {
        return gmdate('Y-m-d', $this->created_at);
    }
}
