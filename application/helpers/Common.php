<?php

namespace app\helpers;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\helpers
 */
class Common
{
    /**
     * @param null $language
     * @return null|string
     */
    public static function getShortLanguage($language = null)
    {
        $language = $language == null ? Yii::$app->language : $language;
        $languageParts = explode('-', $language);
        if (count($languageParts)) {
            return $languageParts[0];
        }

        return $language;
    }

    /**
     * @param $IP
     * @param $CIDR
     * @return bool
     */
    public static function ipCIDRCheck($IP, $CIDR)
    {
        if (strpos($CIDR, '/') == false ) {
            $CIDR .= '/32';
        }

        list ($net, $mask) = explode ('/', $CIDR);

        $ipNet = ip2long($net);
        $ipMask = ~((1 << (32 - $mask)) - 1);
        $ipIP = ip2long($IP);

        return (($ipIP & $ipMask) == ($ipNet & $ipMask));
    }
}
