<?php

namespace youdate\helpers;

use app\helpers\Html;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\helpers
 */
class Icon
{
    /**
     * @param $iconName
     * @param array $options
     * @return string
     */
    public static function fe($iconName, $options = [])
    {
        return self::icon('fe', $iconName, $options);
    }

    /**
     * @param $iconName
     * @param array $options
     * @return string
     */
    public static function fa($iconName,  $options = [])
    {
        return self::icon('fa', $iconName, $options);
    }

    /**
     * @param $iconClass
     * @param $iconName
     * @param array $options
     * @return string
     */
    public static function icon($iconClass, $iconName, $options = [])
    {
        if (isset($options['class'])) {
            $options['class'] = "$iconClass $iconClass-$iconName {$options['class']}";
        } else {
            $options['class'] = "$iconClass $iconClass-$iconName";
        }

        return Html::tag('i', '', $options);
    }
}
