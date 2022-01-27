<?php

namespace app\helpers;

use app\models\HelpCategory;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\helpers
 */
class Html extends \yii\helpers\Html
{
    /**
     * @param $text
     * @return string
     */
    public static function prettyPrinted($text)
    {
        $text = self::encode($text);
        $text = preg_replace("/(\r?\n){2,}/", "\n\n", $text);
        return nl2br($text);
    }

    /**
     * @param HelpCategory[] $helpCategories
     * @param string $category
     * @return array
     */
    public static function prepareHelpCategories($helpCategories, $category)
    {
        $menuItems = [];
        $selectedFirst = false;

        foreach ($helpCategories as $helpCategory) {
            if ($category == null) {
                if ($selectedFirst == false) {
                    $active = true;
                    $selectedFirst = true;
                } else {
                    $active = false;
                }
            } else {
                $active = $category == $helpCategory->alias;
            }
            $menuItems[] = [
                'label' => $helpCategory->title,
                'url' => ['/help/index', 'category' => $helpCategory->alias],
                'icon' => $helpCategory->icon,
                'active' => $active,
            ];
        }

        return $menuItems;
    }
}
