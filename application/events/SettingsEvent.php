<?php

namespace app\events;

use app\base\Event;
use app\settings\Settings;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\events
 * @property Settings $sender
 */
class SettingsEvent extends Event
{
    /**
     * @var bool
     */
    public $replace = false;
    /**
     * @var string
     */
    public $category;
    /**
     * @var string
     */
    public $key;
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var mixed
     */
    public $default = null;
}
