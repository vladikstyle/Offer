<?php

namespace app\models;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property string $country
 * @property string $name
 * @property integer $geoname_id
 */
class Country extends \app\base\ActiveRecord
{
    /**
     * @var string
     */
    public $translation;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'geoname_id'], 'required'],
            [['geoname_id'], 'integer'],
            [['country'], 'string', 'max' => 2],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'country' => Yii::t('app', 'Country Code'),
            'name' => Yii::t('app','Country'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountryTranslations()
    {
        return $this->hasMany(CountryTranslation::class, ['country' => 'country']);
    }
}
