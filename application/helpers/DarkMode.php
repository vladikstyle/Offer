<?php

namespace app\helpers;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\helpers
 */
class DarkMode
{
    const AUTO = 'auto';
    const ALWAYS_LIGHT = 'light';
    const ALWAYS_DARK = 'dark';

    public static $modes = [
        self::AUTO,
        self::ALWAYS_LIGHT,
        self::ALWAYS_DARK,
    ];

    public static function getModesList()
    {
        return [
            self::AUTO => Yii::t('app', 'System default'),
            self::ALWAYS_LIGHT => Yii::t('app', 'Light mode'),
            self::ALWAYS_DARK => Yii::t('app', 'Dark mode'),
        ];
    }
}
