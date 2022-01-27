<?php

namespace app\components\geodata;

use Geocoder\Model\Coordinates;
use MenaraSolutions\Geographer\City;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\Services\DefaultManager;
use Yii;
use yii\base\Component;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\geodata
 */
class GeographerData extends Component implements GeodataInterface
{
    /**
     * @var Earth
     */
    private $earth;
    /**
     * @var Country[]
     */
    private $countries;
    /**
     * @var array
     */
    private $_countriesList;
    /**
     * @var DefaultManager
     */
    private $manager;
    /**
     * @var string
     */
    private $language;

    public function init()
    {
        $this->manager = new DefaultManager();
        $this->manager->setTranslator(
            new GeographerTranslator($this->manager->getStoragePath(), $this->manager->getRepository())
        );
    }

    /**
     * @param $code
     * @return mixed|null
     */
    public function getCountryName($code)
    {
        $list = $this->getCountriesList();
        if (isset($list[$code])) {
            return $list[$code];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getCountriesList()
    {
        if (isset($this->_countriesList)) {
            return $this->_countriesList;
        }
        $list = [];
        foreach ($this->getCountries() as $country) {
            /** @var $country Country */
            $list[$country->getCode()] = $country->getName();
        }
        $this->_countriesList = $list;

        return $list;
    }

    /**
     * @return \MenaraSolutions\Geographer\Collections\MemberCollection|Country[]
     */
    private function getCountries()
    {
        if (!isset($this->countries)) {
            $this->countries = $this->getEarth()->getCountries()->sortBy('name');
        }
        return $this->countries;
    }

    /**
     * @return Earth|mixed
     */
    private function getEarth()
    {
        if (!isset($this->earth)) {
            $this->earth = new Earth();
            $this->earth->setManager($this->manager);
            $this->earth->setLocale(Yii::$app->language);
        }
        return $this->earth;
    }

    /**
     * @param $code
     * @return bool
     */
    public function isValidCountryCode($code)
    {
        try {
            return $this->getCountries()->findOne(['code' => $code]) != null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public function isValidCityCode($code)
    {
        try {
            City::build($code, $this->manager);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $countryCode
     * @param $query
     * @return array
     */
    public function findCities($countryCode, $query)
    {
        $results = [];
        $country = $this->getEarth()->findOne(['code' => $countryCode]);

        if ($country !== null) {
            $states = $country->find();
            foreach ($states as $state) {
                $cities = $state->getCities()->sortBy('name');
                foreach ($cities as $city) {
                    if (strpos($city->getName(), $query) !== false) {
                        $results[] = [
                            'value' => $city->getCode(),
                            'text' => $city->getName(),
                            'city' => $city->getName(),
                            'region' => $state->getName(),
                            'country' => $countryCode,
                            'population' => $city->getPopulation(),
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param $language
     * @return mixed|void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @param $geonameId
     * @param null $language
     * @return null|string
     */
    public function getCityName($geonameId, $language = null)
    {
        try {
            $city = City::build($geonameId, $this->manager);
            if ($city !== null) {
                return $city->setLocale($this->language)->getName();
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * @param $geonameId
     * @return Coordinates|null
     */
    public function getCityCoordinates($geonameId)
    {
        try {
            $city = City::build($geonameId, $this->manager);
            if ($city !== null) {
                return new Coordinates($city->getLatitude(), $city->getLongitude());
            }
        } catch (\Exception $e) {
        }

        return null;
    }
}
