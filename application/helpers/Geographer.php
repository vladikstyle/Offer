<?php

namespace app\helpers;

use app\base\Event;
use app\components\geodata\DatabaseData;
use app\components\geodata\GeodataInterface;
use app\components\geodata\GeographerData;
use app\models\Geoname;
use Geocoder\Collection;
use Geocoder\Geocoder;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle6\Client;
use Wearesho\SimpleCache\Adapter;
use Yii;
use yii\base\Component;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\helpers
 */
class Geographer extends Component
{
    const SOURCE_GEOGRAPHER = 'geographer';
    const SOURCE_MYSQL = 'mysql';

    const EVENT_GET_COUNTRIES = 'getCountries';

    /**
     * @var GeodataInterface
     */
    public $dataManager;
    /**
     * @var string
     */
    public $source;
    /**
     * @var int
     */
    public $cacheDuration = 2628000;
    /**
     * @var string
     */
    public $locale = 'en';
    /**
     * @var Geocoder
     */
    private $geocoder;

    public function init()
    {
        switch ($this->source) {
            case self::SOURCE_MYSQL:
                $className = DatabaseData::class;
                break;
            case self::SOURCE_GEOGRAPHER:
            default:
                $className = GeographerData::class;
                break;
        }

        $this->dataManager = new $className;
        $this->dataManager->setLanguage(Yii::$app->language);
    }

    /**
     * @param $code
     * @return null
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
     * @param $code
     * @return bool
     */
    public function isValidCountryCode($code)
    {
        return $this->dataManager->isValidCountryCode($code);
    }

    /**
     * @param $code
     * @return bool
     */
    public function isValidCityCode($code)
    {
        return $this->dataManager->isValidCityCode($code);
    }

    /**
     * @return array
     */
    public function getCountriesList()
    {
        $list = $this->dataManager->getCountriesList();

        $event = new Event(['extraData' => $list]);
        $this->trigger(self::EVENT_GET_COUNTRIES, $event);
        $list = $event->extraData;

        return $list;
    }

    /**
     * @param $geonameId
     * @return null|string
     */
    public function getCityName($geonameId)
    {
        return $this->dataManager->getCityName($geonameId);
    }

    /**
     * @param $geonameId
     * @return \Geocoder\Model\Coordinates|null
     */
    public function getCityCoordinates($geonameId)
    {
        return $this->dataManager->getCityCoordinates($geonameId);
    }

    /**
     * @param $countryCode
     * @param $query
     * @return array
     */
    public function findCities($countryCode, $query)
    {
        return $this->dataManager->findCities($countryCode, $query);
    }

    /**
     * @return Geocoder|StatefulGeocoder
     */
    public function getGeocoder()
    {
        if (isset($this->geocode)) {
            return $this->geocoder;
        }

        $httpClient = new Client();
        $provider = new FreeGeoIp($httpClient);
        $cacheAdapter = new Adapter();
        $cacheProvider = new ProviderCache($provider, $cacheAdapter, $this->cacheDuration);

        $this->geocoder = new StatefulGeocoder($cacheProvider, $this->locale);

        return $this->geocoder;
    }

    /**
     * @param $query
     * @return Collection|null
     * @throws \Geocoder\Exception\Exception
     */
    public function geocodeQuery($query)
    {
        try {
            return $this->getGeocoder()->geocodeQuery(GeocodeQuery::create($query));
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param $ipAddress
     * @return null|string
     * @throws \Geocoder\Exception\Exception
     */
    public function detectCountry($ipAddress)
    {
        $collection = $this->geocodeQuery($ipAddress);

        if ($collection instanceof Collection) {
            $item = $collection->first();
            return $item->getCountry()->getCode();
        }

        return null;
    }

    /**
     * @param $ipAddress
     * @return \Geocoder\Model\Coordinates|null
     * @throws \Geocoder\Exception\Exception
     */
    public function detectCoordinates($ipAddress)
    {
        $collection = $this->geocodeQuery($ipAddress);

        if ($collection instanceof Collection) {
            $item = $collection->first();
            return $item->getCoordinates();
        }


        return null;
    }

    /**
     * @param $ipAddress
     * @return Geoname|array|null
     * @throws \Geocoder\Exception\Exception
     */
    public function detectCityByIp($ipAddress)
    {
        $coordinates = $this->detectCoordinates($ipAddress);

        if ($coordinates !== null) {
            return $this->detectCityByCoordinates($coordinates);
        }

        return null;
    }

    /**
     * @param Coordinates $coordinates
     * @return Geoname|array|null
     */
    public function detectCityByCoordinates(Coordinates $coordinates)
    {
        return Geoname::find()
            ->withTranslation(Common::getShortLanguage(Yii::$app->language))
            ->addSelect('geoname_translation.name as nameTranslation')
            ->cities()
            ->closest($coordinates)
            ->limit(3)
            ->one();
    }

    /**
     * @param $ipAddress
     * @param string $default
     * @return mixed|string|null
     * @throws \Geocoder\Exception\Exception
     */
    public function detectTimezone($ipAddress, $default = 'UTC')
    {
        $collection = $this->geocodeQuery($ipAddress);

        if ($collection instanceof Collection) {
            $item = $collection->first();
            return $item->getTimezone();
        }

        return $default;
    }
}
