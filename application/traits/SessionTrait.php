<?php

namespace app\traits;

use Yii;
use yii\web\Session;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits
 * @property Session $session
 */
trait SessionTrait
{
    /**
     * @var string
     */
    protected $sessionComponent = 'session';
    /**
     * @var Session
     */
    protected $sessionComponentCached;

    /**
     * @return Session|null|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getSession()
    {
        if (!isset($this->sessionComponentCached)) {
            $this->sessionComponentCached = Yii::$app->get($this->sessionComponent);
        }

        return $this->sessionComponentCached;
    }
}
