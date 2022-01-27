<?php

namespace app\base;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\base
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * @var
     */
    public $totalCountCallback;

    /**
     * @return int|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function prepareTotalCount()
    {
        if (isset($this->totalCountCallback) && is_callable($this->totalCountCallback)) {
            return call_user_func($this->totalCountCallback, $this);
        }

        return parent::prepareTotalCount();
    }
}
