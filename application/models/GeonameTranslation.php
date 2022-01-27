<?php

namespace app\models;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $alternatename_id
 * @property int $geoname_id
 * @property string $language
 * @property string $name
 *
 * @property Geoname $geoname
 */
class GeonameTranslation extends \app\base\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%geoname_translation}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['geoname_id'], 'integer'],
            [['language'], 'string', 'max' => 7],
            [['name'], 'string', 'max' => 200],
            [['geoname_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Geoname::class,
                'targetAttribute' => ['geoname_id' => 'geoname_id']
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'alternatename_id' => 'ID',
            'geoname_id' => Yii::t('app', 'Geoname'),
            'language' => Yii::t('app', 'Language'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeoname()
    {
        return $this->hasOne(Geoname::class, ['geoname_id' => 'geoname_id']);
    }
}
