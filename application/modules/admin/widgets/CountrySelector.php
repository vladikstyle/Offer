<?php

namespace app\modules\admin\widgets;

use Yii;
use app\helpers\Html;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\widgets;
 */
class CountrySelector extends SelectizeDropDownList
{
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'country-selector');
        $this->items = array_merge([null => Yii::t('app', 'Country')], Yii::$app->geographer->getCountriesList());
        $this->clientOptions = [
            'onInitialize' => new \yii\web\JsExpression('function() {
                $(".selectize-input input").attr("autocomplete", "new-password");
            }')
        ];
    }
}
