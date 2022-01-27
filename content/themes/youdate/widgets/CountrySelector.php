<?php

namespace youdate\widgets;

use app\helpers\Html;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class CountrySelector extends SelectizeDropDownList
{
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['class' => 'country-selector']);
        $this->items = array_merge([null => Yii::t('youdate', 'Country')], Yii::$app->geographer->getCountriesList());
        $this->clientOptions = [
            'onInitialize' => new \yii\web\JsExpression('function() {
                $(".selectize-input input").attr("autocomplete", "new-password");
            }')
        ];
    }
}
