<?php

namespace app\traits\managers;

use app\managers\LikeManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property LikeManager $likeManager
 */
trait LikeManagerTrait
{
    /**
     * @var string
     */
    protected $likeManagerComponent = 'likeManager';
    /**
     * @var LikeManager
     */
    protected $likeManagerCached;

    /**
     * @return object|null|LikeManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getLikeManager()
    {
        if (!isset($this->likeManagerCached)) {
            $this->likeManagerCached = Yii::$app->get($this->likeManagerComponent);
        }

        return $this->likeManagerCached;
    }
}
