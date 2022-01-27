<?php

namespace app\forms;

use app\base\Model;
use app\models\Group;
use app\models\User;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\forms
 */
class PostForm extends Model
{
    /**
     * @var string
     */
    public $content;
    /**
     * @var User
     */
    public $user;
    /**
     * @var Group
     */
    public $group;
    /**
     * @var array
     */
    public $attachments = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['content', 'required'],
            ['content', 'string', 'max' => 2000],
            ['attachments', 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'content' => Yii::t('app', 'Content'),
            'attachments' => Yii::t('app', 'Attachments'),
        ];
    }
}
