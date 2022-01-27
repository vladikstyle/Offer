<?php

namespace app\models;

use app\modules\admin\components\Permission;
use app\modules\admin\traits\UserSelectionTrait;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property integer $id
 * @property integer $user_id
 * @property string $role
 * @property string $permissions
 * @property integer $created_at
 * @property integer $updated_at
 * @property User $user
 */
class Admin extends \app\base\ActiveRecord
{
    use UserSelectionTrait;

    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';

    /**
     * @var array
     */
    protected $permissionsArray = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * @return array
     */
    public static function getRoleOptions()
    {
        return [
            self::ROLE_ADMIN => Yii::t('app', 'Administrator'),
            self::ROLE_MODERATOR => Yii::t('app', 'Moderator'),
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        if (isset($this->permissions)) {
            $this->permissionsArray = explode(',', $this->permissions);
        }
    }

    /**
     * @param bool $insert
     * @return bool|void
     */
    public function beforeSave($insert)
    {
        if (is_array($this->permissionsArray)) {
            $this->permissions = implode(',', $this->permissionsArray);
        }

        return true;
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [['role'], 'in', 'range' => array_keys(self::getRoleOptions())],
            [['permissions'], 'string'],
            [['permissionsArray'], 'each', 'rule' => [
                'in', 'range' => array_keys(Permission::getPermissionsList()),
            ]]
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
            'role' => Yii::t('app', 'Role'),
            'permissions' => Yii::t('app', 'Permissions'),
            'permissionsArray' => Yii::t('app', 'Permissions'),
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function getPermissionsArray()
    {
        return $this->permissionsArray;
    }

    /**
     * @param $permissionsArray
     */
    public function setPermissionsArray($permissionsArray)
    {
        $this->permissionsArray = $permissionsArray;
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (!isset($this->permissions)) {
            return true;
        }

        return in_array($permission, $this->permissionsArray);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Add user to admins/moderators
     *
     * @param User $user
     * @param string $role
     * @param null $permissions
     * @return bool
     */
    public static function add(User $user, $role = self::ROLE_ADMIN, $permissions = null)
    {
        $admin = new static();
        $admin->user_id = $user->id;
        $admin->role = $role;
        $admin->permissions = $permissions;

        return $admin->save();
    }

    /**
     * Remove user from admins
     *
     * @param User $user
     * @return bool|false|int
     * @throws \Exception
     * @throws \Throwable
     */
    public static function remove(User $user)
    {
        $admin = self::findOne(['user_id' => $user->id]);
        if ($admin !== null) {
            return $admin->delete();
        }

        return false;
    }
}
