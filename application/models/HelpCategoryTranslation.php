<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property int $help_category_id
 * @property string $language
 * @property string $title
 * @property int $created_at
 * @property int $updated_at
 *
 * @property HelpCategory $helpCategory
 */
class HelpCategoryTranslation extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%help_category_translation}}';
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
            [['help_category_id', 'language', 'title'], 'required'],
            [['help_category_id', 'created_at', 'updated_at'], 'integer'],
            [['language'], 'string', 'max' => 6],
            [['title'], 'string', 'max' => 255],
            [['help_category_id'], 'exist', 'skipOnError' => true,
                'targetClass' => HelpCategory::class,
                'targetAttribute' => ['help_category_id' => 'id']
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
            'help_category_id' => Yii::t('app', 'Help Category'),
            'language' => Yii::t('app', 'Language'),
            'title' => Yii::t('app', 'Title'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpCategory()
    {
        return $this->hasOne(HelpCategory::class, ['id' => 'help_category_id']);
    }
}
