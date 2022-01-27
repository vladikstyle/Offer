<?php

namespace app\traits\managers;

use app\managers\BalanceManager;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits\managers
 * @property BalanceManager $balanceManager
 */
trait BalanceManagerTrait
{
    /**
     * @var string
     */
    protected $balanceManagerComponent = 'balanceManager';
    /**
     * @var BalanceManager
     */
    protected $balanceManagerCached;

    /**
     * @return object|null|BalanceManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getBalanceManager()
    {
        if (!isset($this->balanceManagerCached)) {
            $this->balanceManagerCached = Yii::$app->get($this->balanceManagerComponent);
        }

        return $this->balanceManagerCached;
    }
}
