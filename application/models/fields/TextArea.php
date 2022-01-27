<?php

namespace app\models\fields;

use Yii;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\fields
 */
class TextArea extends Text
{
    /**
     * @param ActiveForm $form
     * @param $model
     * @param array $options
     * @return mixed
     */
    public function getFieldInput($form, $model, $options = [])
    {
        return $form->field($model, $this->profileField->alias)->textarea($options);
    }

    /**
     * @param ActiveForm $form
     * @param $model
     * @param $searchAttribute
     * @param bool $userHasPremium
     * @param array $options
     * @return \yii\widgets\ActiveField
     */
    public function getFieldSearchInputs($form, $model, $searchAttribute, $userHasPremium = false, $options = [])
    {
        return parent::getFieldSearchInputs($form, $model, $searchAttribute, $userHasPremium, $options)->textarea($options);
    }
}
