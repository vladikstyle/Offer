<?php

namespace app\forms;

use app\models\Profile;
use app\models\Sex;
use app\traits\CountryTrait;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\forms
 */
class UserSearchForm extends \yii\base\Model
{
    use CountryTrait;

    const LOCATION_TYPE_ADDRESS = 'address';
    const LOCATION_TYPE_NEAR = 'near';

    const DISTANCE_EVERYWHERE = 0;

    /**
     * @var integer
     */
    public $sex;
    /**
     * @var integer
     */
    public $fromAge = 18;
    /**
     * @var integer
     */
    public $toAge = 100;
    /**
     * @var integer
     */
    public $status;
    /**
     * @var string
     */
    public $country;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $locationType = self::LOCATION_TYPE_NEAR;
    /**
     * @var integer
     */
    public $distance = 50; // km
    /**
     * @var string
     */
    public $latitude;
    /**
     * @var string
     */
    public $longitude;
    /**
     * @var bool
     */
    public $online;
    /**
     * @var bool
     */
    public $verified;
    /**
     * @var bool
     */
    public $withPhoto;
    /**
     * @var array
     */
    public $extraFields = [];

    public function init()
    {
        if ($this->isOneCountryOnly()) {
            $this->country = $this->getDefaultCountry();
        }

        $preferWithPhotos = Yii::$app->settings->get('frontend', 'sitePreferUsersWithPhoto');
        if ($preferWithPhotos) {
            $this->withPhoto = true;
        }

        parent::init();
    }

    /**
     * @param Profile $profile
     */
    public function setProfile($profile)
    {
        if ($profile === null) {
            return;
        }

        $this->sex = $profile->looking_for_sex;
        $this->country = $profile->country;
        $this->city = $profile->city;
        $this->latitude = $profile->latitude;
        $this->longitude = $profile->longitude;
        if (isset($profile->looking_for_from_age) && $profile->looking_for_from_age) {
            $this->fromAge = $profile->looking_for_from_age;
        }
        if (isset($profile->looking_for_to_age) && $profile->looking_for_to_age) {
            $this->toAge = $profile->looking_for_to_age;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function rules()
    {
        $rules = [
            ['sex', 'in', 'range' => array_keys($this->getSexModels())],
            [['fromAge', 'toAge'], 'integer', 'min' => 18, 'max' => 100],
            ['city', 'integer', 'min' => 0],
            ['locationType', 'in', 'range' => array_keys($this->getLocationTypeOptions())],
            ['locationType', 'safe'],
            ['distance', 'integer', 'min' => 0, 'max' => 10000],
            [['online', 'verified', 'withPhoto'], 'boolean'],
            ['withPhoto', 'default', 'value' => 1],
            ['extraFields', 'each', 'rule' => [
                'string', 'min' => 1,
            ]],
        ];

        if ($this->isOneCountryOnly() == false) {
            $rules[] = ['country', 'string', 'min' => 2, 'max' => 2];
            $rules[] = ['country', function($attribute, $params) {
                return Yii::$app->geographer->isValidCountryCode($this->$attribute);
            }];
        }

        return $rules;
    }

    /**
     * @return Sex[]
     */
    public function getSexModels()
    {
        $data = [Profile::SEX_NOT_SET => Yii::t('app', 'Whatever')];
        $sexOptions = Sex::find()->all();
        foreach ($sexOptions as $sexOption) {
            $data[$sexOption->sex] = $sexOption;
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getLocationTypeOptions()
    {
        return [
            self::LOCATION_TYPE_NEAR => Yii::t('app', 'Near me'),
            self::LOCATION_TYPE_ADDRESS => $this->isOneCountryOnly() ?
                Yii::t('app', 'City') :
                Yii::t('app', 'Country and city'),
        ];
    }

    /**
     * @return array
     */
    public function getDistanceOptions()
    {
        return [
            10 => 10,
            50 => 50,
            250 => 250,
            0 => Yii::t('app', 'Everywhere')
        ];
    }

    /**
     * @param $value
     * @param bool $plural
     * @return mixed|null
     */
    public function getSexTitle($value, $plural = true)
    {
        $values = $this->getSexModels();
        return isset($values[$value]) ? ($plural ? $values[$value]->title_plural : $values[$value]->title) : null;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'sex' => Yii::t('app', 'Sex'),
            'online' => Yii::t('app', 'Online users'),
            'verified' => Yii::t('app', 'Verified users'),
            'withPhoto' => Yii::t('app', 'With photo'),
            'extraFields' => Yii::t('app', 'Custom search'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
