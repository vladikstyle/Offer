<?php

namespace app\models;

use app\models\query\HelpQuery;
use omgdef\multilingual\MultilingualBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property int $help_category_id
 * @property int $sort_order
 * @property boolean $is_active
 * @property string $title
 * @property string $content
 * @property int $created_at
 * @property int $updated_at
 *
 * @property HelpTranslation[] $helpTranslations
 * @property HelpCategory $helpCategory
 */
class Help extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%help}}';
    }

    /**
     * @return HelpQuery|\yii\db\ActiveQuery|object
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(HelpQuery::class, [get_called_class()]);
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
            'translations' => [
                'class' => MultilingualBehavior::class,
                'languages' => Language::getLanguageNames(true, true),
                'languageField' => 'language',
                'requireTranslations' => false,
                'dynamicLangClass' => false,
                'langClassName' => HelpTranslation::class,
                'defaultLanguage' => 'en',
                'langForeignKey' => 'help_id',
                'tableName' => '{{%help_translation}}',
                'attributes' => ['title', 'content']
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['help_category_id', 'sort_order'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => true],
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
            'sort_order' => Yii::t('app', 'Sort Order'),
            'is_active' => Yii::t('app', 'Active'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpTranslations()
    {
        return $this->hasMany(HelpTranslation::class, ['help_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpCategory()
    {
        return $this->hasOne(HelpCategory::class, ['id' => 'help_category_id']);
    }
}
