<?php

namespace app\models\query;

use app\models\Geoname;
use Geocoder\Model\Coordinates;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class GeonameQuery extends \yii\db\ActiveQuery
{
    /**
     * @return $this|GeonameQuery
     */
    public function cities()
    {
        return $this->andWhere(['in', 'fclass', 'p']);
    }

    /**
     * @param Coordinates $coordinates
     * @param int $distance
     * @return $this
     */
    public function closest(Coordinates $coordinates, $distance = 10)
    {
        $latitude = $coordinates->getLatitude();
        $longitude = $coordinates->getLongitude();

        $this->addSelect(['geoname.*', '(6371 * acos(cos(radians(:latitude))
                          * cos( radians(geoname.latitude))
                          * cos( radians(geoname.longitude) - radians(:longitude))
                          + sin ( radians(:latitude))
                          * sin( radians( geoname.latitude))
                        )) AS distance']);

        $this->andHaving('distance <= :distance', [':distance' => $distance]);
        $this->orderBy('distance');
        $this->addParams([
            'latitude' => number_format($latitude, 2, '.', ''),
            'longitude' => number_format($longitude, 2, '.', ''),
        ]);

        return $this;
    }

    /**
     * @param $language
     * @return GeonameQuery
     */
    public function withTranslation($language)
    {
        return $this->joinWith('geonameTranslation', false)
            ->addParams([':language' => $language]);
    }

    /**
     * @param $country
     * @return GeonameQuery
     */
    public function whereCountry($country)
    {
        return $this->andWhere(['country' => $country]);
    }

    /**
     * @inheritdoc
     * @return Geoname[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Geoname|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
