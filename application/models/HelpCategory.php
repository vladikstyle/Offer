<?php

namespace app\models;

use app\models\query\HelpCategoryQuery;
use omgdef\multilingual\MultilingualBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property string $alias
 * @property string $title
 * @property int $sort_order
 * @property boolean $is_active
 * @property int $created_at
 * @property int $updated_at
 * @property string $icon
 *
 * @property HelpCategoryTranslation[] $helpCategoryTranslations
 */
class HelpCategory extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%help_category}}';
    }

    /**
     * @return HelpCategoryQuery|\yii\db\ActiveQuery|object
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(HelpCategoryQuery::class, [get_called_class()]);
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
                'langClassName' => HelpCategoryTranslation::class,
                'defaultLanguage' => 'en',
                'langForeignKey' => 'help_category_id',
                'tableName' => '{{%help_category_translation}}',
                'attributes' => ['title']
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['alias', 'title'], 'required'],
            [['sort_order', 'created_at', 'updated_at'], 'integer'],
            [['alias', 'title'], 'string', 'max' => 64],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => true],
            [['icon'], 'string', 'max' => 64],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alias' => Yii::t('app', 'Alias'),
            'title' =>  Yii::t('app', 'Title'),
            'sort_order' =>  Yii::t('app', 'Sort Order'),
            'is_active' => Yii::t('app', 'Active'),
            'icon' => Yii::t('app', 'Icon'),
            'created_at' =>  Yii::t('app', 'Created At'),
            'updated_at' =>  Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpCategoryTranslations()
    {
        return $this->hasMany(HelpCategoryTranslation::class, ['help_category_id' => 'id']);
    }
}
