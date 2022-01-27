<?php

namespace app\traits;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits
 */
trait CountryTrait
{
    /**
     * @var bool
     */
    private $_isOneCountryOnly;
    /**
     * @var string
     */
    private $_countryDefault;

    /**
     * @return bool
     * @throws \Exception
     */
    public function isOneCountryOnly()
    {
        if (!isset($this->_isOneCountryOnly)) {
            $this->_isOneCountryOnly = Yii::$app->settings->get('frontend', 'siteOneCountryOnly', false);
        }

        return $this->_isOneCountryOnly;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDefaultCountry()
    {
        if (!isset($this->_countryDefault)) {
            $this->_countryDefault = Yii::$app->settings->get('frontend', 'siteCountry');
        }

        return $this->_countryDefault;
    }

    /**
     * @param $country
     * @param $city
     * @return string
     * @throws \Exception
     */
    public function getLocationString($country, $city)
    {
        $parts = [];
        if (!empty($country) && $this->isOneCountryOnly() == false) {
            $parts[] = Yii::$app->geographer->getCountryName($country);
        }
        if (!empty($city)) {
            $parts[] = Yii::$app->geographer->getCityName($city);
        }
        if (count($parts)) {
            return implode(', ', $parts);
        }

        return count($parts) ?  implode(', ', $parts) : '';
    }
}
