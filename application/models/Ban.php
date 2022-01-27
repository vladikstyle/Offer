<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\validators\IpValidator;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property string $ip
 * @property int $created_at
 * @property int $updated_at
 */
class Ban extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%ban}}';
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
            [['ip'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['ip'], 'string', 'max' => 32],
            [['ip'], 'unique'],
            [['ip'], IpValidator::class, 'subnet' => null],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'IP',
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
