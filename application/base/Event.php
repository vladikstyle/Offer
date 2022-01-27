<?php

namespace app\base;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\base
 */
class Event extends \yii\base\Event
{
    /**
     * @var mixed
     */
    public $extraData;
    /**
     * @var bool
     */
    public $isValid = true;
    /**
     * @var boolean
     */
    public $override = false;
}
