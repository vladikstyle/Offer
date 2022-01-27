<?php

namespace app\events;

use app\base\Event;
use app\models\fields\BaseType;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\events
 * @property BaseType $sender
 */
class ProfileFieldEvent extends Event
{
}
