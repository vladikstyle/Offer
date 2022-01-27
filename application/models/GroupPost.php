<?php

namespace app\models;

use app\models\query\GroupPostQuery;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property int $id
 * @property int $group_id
 * @property int $post_id
 * @property Group $group
 * @property Post $post
 */
class GroupPost extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     * @return GroupPostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GroupPostQuery(get_called_class());
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%group_post}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['group_id', 'post_id'], 'integer'],
            [['group_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Group::class,
                'targetAttribute' => ['group_id' => 'id']
            ],
            [['post_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Post::class,
                'targetAttribute' => ['post_id' => 'id']
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
            'post_id' => Yii::t('app', 'Post'),
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
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }
}
