<?php

namespace app\traits\managers;

use app\managers\PhotoManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property PhotoManager $photoManager
 */
trait PhotoManagerTrait
{
    /**
     * @var string
     */
    protected $photoManagerComponent = 'photoManager';
    /**
     * @var PhotoManager
     */
    protected $photoManagerCached;

    /**
     * @return object|null|PhotoManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhotoManager()
    {
        if (!isset($this->photoManagerCached)) {
            $this->photoManagerCached = Yii::$app->get($this->photoManagerComponent);
        }

        return $this->photoManagerCached;
    }
}
