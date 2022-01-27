<?php

namespace app\models;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property string $country
 * @property string $language
 * @property string $translation
 */
class CountryTranslation extends \app\base\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%country_translation}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['language', 'translation'], 'required'],
            [['country'], 'string', 'max' => 2],
            [['language'], 'string', 'max' => 6],
            [['translation'], 'string', 'max' => 255],
            [['country', 'language'], 'unique', 'targetAttribute' => ['country', 'language']],
            [['country'], 'exist', 'skipOnError' => true,
                'targetClass' => Country::class,
                'targetAttribute' => ['country' => 'country']
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'country' => Yii::t('app', 'Country'),
            'language' => Yii::t('app', 'Language'),
            'translation' => Yii::t('app', 'Translation'),
        ];
    }
}
