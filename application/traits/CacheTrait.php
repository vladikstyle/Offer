<?php

namespace app\traits;

use Yii;
use yii\caching\Cache;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits
 * @property Cache $cache
 */
trait CacheTrait
{
    /**
     * @var string
     */
    protected $cacheComponent = 'cache';
    /**
     * @var Cache
     */
    protected $cacheComponentCached;

    /**
     * @return Cache|null|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getCache()
    {
        if (!isset($this->cacheComponentCached)) {
            $this->cacheComponentCached = Yii::$app->get($this->cacheComponent);
        }

        return $this->cacheComponentCached;
    }
}
