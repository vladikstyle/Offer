<?php

namespace app\models;

use app\models\query\GroupUserQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\model
 *
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $status
 * @property string $role
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Group $group
 * @property User $user
 * @property Profile $userProfile
 */
class GroupUser extends \app\base\ActiveRecord
{
    const STATUS_MEMBER = 'member';
    const STATUS_UNDER_MODERATION = 'under_moderation';
    const STATUS_BANNED = 'banned';

    const ROLE_MEMBER = 'member';
    const ROLE_ADMIN = 'admin';

    /**
     * @return object|\yii\db\ActiveQuery|GroupUserQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(GroupUserQuery::class, [GroupUser::class]);
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%group_user}}';
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
            [['group_id', 'user_id'], 'required'],
            [['group_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['status', 'role'], 'string', 'max' => 64],
            [['group_id', 'user_id'], 'unique', 'targetAttribute' => ['group_id', 'user_id']],
            [['group_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Group::class,
                'targetAttribute' => ['group_id' => 'id']
            ],
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
            'group_id' => Yii::t('app', 'Group'),
            'user_id' => Yii::t('app', 'User'),
            'status' => Yii::t('app', 'Status'),
            'role' => Yii::t('app', 'Role'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'user_id']);
    }

    /**
     * @param bool $save
     * @return bool
     */
    public function approve($save = true)
    {
        $this->status = self::STATUS_MEMBER;

        if ($save) {
            return $this->save();
        }

        return true;
    }

    /**
     * @param bool $save
     * @return bool
     */
    public function toggleBan($save = true)
    {
        $this->status = $this->status === self::STATUS_BANNED ? self::STATUS_MEMBER : self::STATUS_BANNED;

        if ($save) {
            return $this->save();
        }

        return true;
    }

    /**
     * @param bool $save
     * @return bool
     */
    public function toggleAdmin($save = true)
    {
        $this->role = $this->role === self::ROLE_ADMIN ? self::ROLE_MEMBER : self::ROLE_ADMIN;

        if ($save) {
            return $this->save();
        }

        return true;
    }
}
