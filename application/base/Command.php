<?php

namespace app\base;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\base
 */
class Command extends \yii\console\Controller
{
    /**
     * @var bool
     */
    public $minimal = false;

    /**
     * @param string $actionID
     * @return array|string[]
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'minimal',
        ]);
    }
}
