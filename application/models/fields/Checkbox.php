<?php

namespace app\models\fields;

use app\base\Model;
use app\forms\UserSearchForm;
use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\fields
 */
class Checkbox extends BaseType
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return array_merge(parent::getFieldOptions(), [

        ]);
    }

    /**
     * @return array
     */
    public function getFieldRules()
    {
        $rules = [];

        // form rules

        $rules[$this->profileField->alias . '.inRange'] = [
            $this->profileField->alias, 'in', 'range' => [0, 1],
            'on' => Model::SCENARIO_FORM,
        ];

        $rules[$this->profileField->alias . '.default'] = [
            $this->profileField->alias,
            'default', 'value' => $this->profileField,
            'on' => Model::SCENARIO_FORM,
        ];

        // search rules

        $rules[$this->profileField->alias . '.search'] = [
            $this->profileField->alias,
            'safe',
            'on' => Model::SCENARIO_SEARCH,
        ];

        return array_merge(parent::getFieldRules(), $rules);
    }

    /**
     * @param ActiveForm $form
     * @param $model
     * @param array $options
     * @return mixed
     */
    public function getFieldInput($form, $model, $options = [])
    {
        return $form->field($model, $this->profileField->alias)->checkbox($options);
    }

    /**
     * @param ActiveForm $form
     * @param UserSearchForm $model
     * @param $searchAttribute
     * @param bool $userHasPremium
     * @param array $options
     * @return \yii\widgets\ActiveField
     */
    public function getFieldSearchInputs($form, $model, $searchAttribute, $userHasPremium = false, $options = [])
    {
        $formName = "{$searchAttribute}[{$this->profileField->id}]";

        if (!$userHasPremium && $this->profileField->searchable_premium == true) {
            $options = array_merge($options, [
                'disabled' => 'disabled',
                'premium' => 'premium',
                'placeholder' => Yii::t('app', 'Premium only'),
                'rel' => 'tooltip',
                'title' => Yii::t('app', 'Activate premium account to use this search criteria'),
            ]);
        }

        $options['value'] = isset($model->{$searchAttribute}[$this->profileField->id]) ? 1 : 0;
        $options['uncheck'] = 0;

        return $form->field($model, $formName)->checkbox($options)->label($this->profileField->getFieldTitle());
    }

    /**
     * @param \yii\db\Query $query
     * @param string $searchValue
     * @throws \Exception
     */
    public function applySearchQuery($query, $searchValue)
    {
        $searchKeysCount = ArrayHelper::getValue($query->params, 'searchKeysCount', 0);

        if ($searchValue == true) {
            $query->orWhere(['profile_extra.field_id' => $this->profileField->id, 'profile_extra.value' => 1])
                ->addParams(['searchKeysCount' => $searchKeysCount + 1]);
        }
    }

    /**
     * @param $value
     * @param bool $raw
     * @return string
     */
    public function formatValue($value, $raw = false)
    {
        if ($raw) {
            return $value;
        }

        return $value == true ? Yii::t('app', 'Yes') : Yii::t('app', 'No');
    }
}
