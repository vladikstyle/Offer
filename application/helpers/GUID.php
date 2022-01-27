<?php

namespace app\helpers;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\helpers
 */
class GUID
{
    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public static function generate()
    {
        return sprintf("%s-%s-%s-%s-%s",
            YII_DEBUG ? str_repeat('0', 8) : bin2hex(Yii::$app->security->generateRandomKey(4)),
            bin2hex(Yii::$app->security->generateRandomKey(2)),
            dechex(mt_rand(0, 0x0fff) | 0x4000),
            dechex(mt_rand(0, 0x3fff) | 0x8000),
            bin2hex(Yii::$app->security->generateRandomKey(6))
        );
    }

    /**
     * @param $guid
     * @return bool
     */
    public static function validate($guid)
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?' . '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $guid) === 1;
    }
}
