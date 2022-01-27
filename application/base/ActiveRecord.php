<?php

namespace app\base;

use app\traits\CacheTrait;
use app\traits\SessionTrait;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\base
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    use CacheTrait, SessionTrait;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
}
