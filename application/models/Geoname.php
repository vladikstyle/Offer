<?php

namespace app\models;

use app\models\query\GeonameQuery;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property int $geoname_id
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property string $fclass
 * @property string $fcode
 * @property string $country
 * @property int $population
 * @property int $adm1_geoname_id
 *
 * @property GeonameTranslation[] $geonameTranslations
 */
class Geoname extends \app\base\ActiveRecord
{
    /**
     * @var string
     */
    public $nameTranslation;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%geoname}}';
    }

    /**
     * @inheritdoc
     * @return GeonameQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GeonameQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['latitude', 'longitude'], 'number'],
            [['population', 'adm1_geoname_id'], 'integer'],
            [['name', 'nameTranslation'], 'string', 'max' => 200],
            [['fclass'], 'string', 'max' => 1],
            [['fcode'], 'string', 'max' => 10],
            [['country'], 'string', 'max' => 2],
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
            'geoname_id' => 'ID',
            'name' => Yii::t('app', 'Name'),
            'latitude' => Yii::t('app', 'Latitude'),
            'longitude' => Yii::t('app', 'Longitude'),
            'fclass' => 'Fclass',
            'fcode' => 'Fcode',
            'country' => Yii::t('app', 'Country'),
            'population' => Yii::t('app', 'Population'),
            'adm1_geoname_id' => Yii::t('app', 'Region'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeonameTranslations()
    {
        return $this->hasMany(GeonameTranslation::class, ['geoname_id' => 'geoname_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeonameTranslation()
    {
        return $this->hasOne(GeonameTranslation::class, ['geoname_id' => 'geoname_id'])
            ->andOnCondition('geoname_translation.language = :language');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return !empty($this->nameTranslation) ? $this->nameTranslation : $this->name;
    }
}
