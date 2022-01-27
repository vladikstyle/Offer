<?php

namespace youdate\components;

use Yii;
use youdate\widgets\SelectizeDropDownList;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\components
 */
class MultiSelect extends \app\models\fields\MultiSelect
{
    /**
     * @param \yii\widgets\ActiveForm $form
     * @param \app\forms\ProfileExtraForm $model
     * @param array $options
     * @return mixed|\yii\widgets\ActiveField
     * @throws \Exception
     */
    public function getFieldInput($form, $model, $options = [])
    {
        $options['multiple'] = true;

        try {
            $values = json_decode($model->{$this->profileField->alias});
        } catch (\Exception $exception) {
            $values = [];
        }

        if (is_array($values)) {
            $options['options'] = [];
            foreach ($values as $value) {
                $options['options'][$value] = ['selected' => true];
            }
        }

        return $form->field($model, $this->profileField->alias)->widget(SelectizeDropDownList::class, [
            'items' => $this->getSelectItems(),
            'options' => $options,
        ]);
    }

    /**
     * @param \yii\widgets\ActiveForm $form
     * @param \app\forms\UserSearchForm $model
     * @param $searchAttribute
     * @param false $userHasPremium
     * @param array $options
     * @return \yii\widgets\ActiveField
     * @throws \Exception
     */
    public function getFieldSearchInputs($form, $model, $searchAttribute, $userHasPremium = false, $options = [])
    {
        $options['prompt'] = '';
        $options['multiple'] = true;

        if (!$userHasPremium && $this->profileField->searchable_premium == true) {
            $options = array_merge($options, [
                'disabled' => 'disabled',
                'prompt' => Yii::t('youdate', 'Premium only'),
                'rel' => 'tooltip',
                'title' => Yii::t('youdate', 'Activate premium account to use this search criteria'),
            ]);
        }

        $formName = "{$searchAttribute}[{$this->profileField->id}]";

        return $form->field($model, $formName)->widget(SelectizeDropDownList::class, [
            'items' => $this->getSelectItems(),
            'options' => $options,
        ])->label($this->profileField->getFieldTitle());
    }
}
