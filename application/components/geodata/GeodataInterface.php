<?php

namespace app\components\geodata;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\geodata
 */
interface GeodataInterface
{
    /**
     * @param $code
     * @return bool
     */
    public function isValidCountryCode($code);
    /**
     * @param $code
     * @return mixed
     */
    public function isValidCityCode($code);
    /**
     * @return array
     */
    public function getCountriesList();
    /**
     * @param $geonameId
     * @return null|string
     */
    public function getCityName($geonameId);
    /**
     * @param $geonameId
     * @return \Geocoder\Model\Coordinates|null
     */
    public function getCityCoordinates($geonameId);
    /**
     * @param $language
     * @return mixed
     */
    public function setLanguage($language);
    /**
     * @param $countryCode
     * @param $query
     * @return array
     */
    public function findCities($countryCode, $query);
}
