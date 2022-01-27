<?php

namespace app\components\geodata;

use app\base\Event;
use app\helpers\Common;
use app\models\Country;
use app\models\Geoname;
use app\traits\CacheTrait;
use Geocoder\Model\Coordinates;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\geodata
 */
class DatabaseData extends Component implements GeodataInterface
{
    use CacheTrait;

    const EVENT_GET_COUNTRIES = 'getCountries';
    const CACHE_COUNTRIES_KEY = 'cachedCountries';

    /**
     * @var string
     */
    private $language;
    /**
     * @var Country[]
     */
    private $countries;
    /**
     * @var array
     */
    private $_countriesList;

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
            $list[$country->country] = $country->translation ?? $country->name;
        }

        $event = new Event(['extraData' => $list]);
        $this->trigger(self::EVENT_GET_COUNTRIES, $event);
        $this->_countriesList = $event->extraData;

        return $this->_countriesList;
    }

    /**
     * @return Country[]
     */
    private function getCountries()
    {
        if (isset($this->countries)) {
            return $this->countries;
        }

        $cacheKey = self::CACHE_COUNTRIES_KEY . Yii::$app->language;
        $countries = $this->cache->get($cacheKey);
        if ($countries == null) {
            /** @var \app\models\Country[] $countries */
            $countries = Country::find()
                ->leftJoin('country_translation', 'country_translation.country = country.country and language = :language', [
                    'language' => Common::getShortLanguage(),
                ])
                ->select('country.country, country.name, country_translation.translation')
                ->orderBy('translation')
                ->all();
            $this->cache->set($cacheKey, $countries);
        }

        $this->countries = $countries;

        return $countries;
    }

    /**
     * @param $code
     * @return bool
     */
    public function isValidCountryCode($code)
    {
        return Country::find()->where(['country' => $code])->count() > 0;
    }

    /**
     * @param $code
     * @return bool
     */
    public function isValidCityCode($code)
    {
        return Geoname::find()->where(['geoname_id' => $code])->count() > 0;
    }

    /**
     * @param $language
     * @return mixed|void
     */
    public function setLanguage($language)
    {
        $this->language = Common::getShortLanguage($language);
    }

    /**
     * @param $countryCode
     * @param $query
     * @return array
     */
    public function findCities($countryCode, $query)
    {
        $results = [];
        $geonames = Geoname::find()
            ->select(['geoname.*', 'geoname_translation.name as nameTranslation'])
            ->cities()
            ->whereCountry($countryCode)
            ->withTranslation($this->language)
            ->andWhere(['or',
                ['like', 'geoname.name', $query],
                ['like', 'geoname_translation.name', $query]
            ])
            ->limit(50)
            ->all();

        $adminGeonames = Geoname::find()
            ->select(['geoname.*', 'geoname_translation.name as nameTranslation'])
            ->whereCountry($countryCode)
            ->withTranslation($this->language)
            ->andWhere(['in', 'geoname.geoname_id', ArrayHelper::getColumn($geonames, 'adm1_geoname_id')])
            ->indexBy('adm1_geoname_id')
            ->limit(20)
            ->all();

        usort($geonames, function($a, $b) {
            return $b->population <=> $a->population;
        });

        foreach ($geonames as $geoname) {
            $adminGeonameTranslation = null;
            if (isset($adminGeonames[$geoname->adm1_geoname_id])) {
                $adminGeonameTranslation = $adminGeonames[$geoname->adm1_geoname_id]->getName();
            }
            $results[] = [
                'value' => $geoname->geoname_id,
                'text' => $geoname->getName(), // for old theme versions
                'city' => $geoname->getName(),
                'region' => $adminGeonameTranslation,
                'country' => $geoname->country,
                'population' => $geoname->population,
            ];
        }

        return $results;
    }

    /**
     * @param $geonameId
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getCityName($geonameId)
    {
        $cacheKey = 'geoname' . $geonameId . Yii::$app->language;

        $cityName = $this->cache->get($cacheKey);
        if ($cityName !== false) {
            return $cityName;
        }

        $geoname = Geoname::find()
            ->select(['geoname.*', 'geoname_translation.name as nameTranslation'])
            ->withTranslation($this->language)
            ->andWhere(['geoname.geoname_id' => $geonameId])->one();

        if ($geoname !== null) {
            $cityName = $geoname->getName();
            $this->cache->set($cacheKey, $cityName, 86400);
            return $cityName;
        }

        return null;
    }

    /**
     * @param $geonameId
     * @return Coordinates|null
     */
    public function getCityCoordinates($geonameId)
    {
        $geoname = Geoname::find()
            ->andWhere(['geoname.geoname_id' => $geonameId])
            ->one();

        if ($geoname == null) {
            return null;
        }

        try {
            return new Coordinates($geoname->latitude, $geoname->longitude);
        } catch (\Exception $e) {
            return null;
        }
    }
}
