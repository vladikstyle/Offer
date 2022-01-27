<?php

namespace app\models\fields;

use app\base\ActiveRecord;
use app\base\Model;
use app\forms\UserSearchForm;
use app\helpers\Html;
use Yii;
use yii\helpers\ArrayHelper;
use youdate\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\fields
 */
class Number extends BaseType
{
    /**
     * @var bool
     */
    public $integerOnly;
    /**
     * @var string|float|integer
     */
    public $maxValue;

    /**
     * @var string|float|integer
     */
    public $minValue;
    /**
     * @var string
     */
    public $prefix;
    /**
     * @var string
     */
    public $postfix;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['maxValue', 'minValue'], 'number'],
            [['prefix', 'postfix'], 'string', 'max' => 255],
            [['integerOnly'], 'boolean'],
        ];
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return array_merge(parent::getFieldOptions(), [
            'integerOnly' => [
                'type' => 'checkbox',
                'label' => Yii::t('app', 'Integers only'),
            ],
            'minValue' => [
                'type' => 'text',
                'label' => Yii::t('app', 'Minimum value'),
            ],
            'maxValue' => [
                'type' => 'text',
                'label' => Yii::t('app', 'Maximum value'),
            ],
            'prefix' => [
                'type' => 'text',
                'label' => Yii::t('app', 'Prefix text'),
            ],
            'postfix' => [
                'type' => 'text',
                'label' => Yii::t('app', 'Postfix text'),
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getFieldRules()
    {
        $rules = [];

        // form rules

        $rules[$this->profileField->alias . '.number'] = [
            $this->profileField->alias,
            'number', 'integerOnly' => $this->integerOnly,
            'on' => Model::SCENARIO_FORM,
        ];
        if ($this->maxValue) {
            $rules[$this->profileField->alias . '.maxValue'] = [
                $this->profileField->alias,
                'number', 'max' => $this->maxValue,
                'on' => Model::SCENARIO_FORM,
            ];
        }
        if ($this->minValue) {
            $rules[$this->profileField->alias . '.minValue'] = [
                $this->profileField->alias,
                'number', 'min' => $this->minValue,
                'on' => Model::SCENARIO_FORM,
            ];
        }

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
        return $form->field($model, $this->profileField->alias)->textInput($options);
    }

    /**
     * @param \yii\widgets\ActiveForm $form
     * @param UserSearchForm $model
     * @param $searchAttribute
     * @param bool $userHasPremium
     * @param array $options
     * @return array|\yii\widgets\ActiveField
     */
    public function getFieldSearchInputs($form, $model, $searchAttribute, $userHasPremium = false, $options = [])
    {
        $formAttributeName = "{$searchAttribute}[{$this->profileField->id}]";

        if (!$userHasPremium && $this->profileField->searchable_premium == true) {
            $options = array_merge($options, [
                'disabled' => 'disabled',
                'value' => '',
                'placeholder' => Yii::t('app', 'Premium only'),
                'rel' => 'tooltip',
                'title' => Yii::t('app', 'Activate premium account to use this search criteria'),
            ]);

            return $form->field($model, $formAttributeName)->textInput($options)->label($this->profileField->getFieldTitle());
        }

        $options['prompt'] = '';
        $minValue = $maxValue = null;
        foreach ($this->getFieldRules() as $rule) {
            if ($rule[1] == 'number' && isset($rule['min'])) {
                $minValue = $rule['min'];
            }
            if ($rule[1] == 'number' && isset($rule['max'])) {
                $maxValue = $rule['max'];
            }
        }

        if ($minValue && $maxValue) {
            $selectOptions = [];
            for ($value = $minValue; $value < $maxValue; $value++) {
                $selectOptions[$value] = $this->formatValue($value);
            }
            return [
                $form->field($model, $formAttributeName . '[0]')->dropDownList($selectOptions, $options)
                    ->label( $this->profileField->getFieldTitle() . ' ' . Yii::t('app', '(min)')),
                $form->field($model, $formAttributeName . '[1]')->dropDownList($selectOptions, $options)
                    ->label($this->profileField->getFieldTitle() . ' ' . Yii::t('app', '(max)')),
            ];
        }

        return $form->field($model, $formAttributeName)->textInput($options)->label($this->profileField->getFieldTitle());
    }

    /**
     * @param \yii\db\Query $query
     * @param string $searchValue
     * @throws \Exception
     */
    public function applySearchQuery($query, $searchValue)
    {
        $id = $this->profileField->id;
        $minAttribute = $this->prepareQueryParam('Min');
        $maxAttribute = $this->prepareQueryParam('Max');
        $searchKeysCount = ArrayHelper::getValue($query->params, 'searchKeysCount', 0);

        if (is_string($searchValue)) {

            // exact search
            $query->orWhere(['profile_extra.field_id' => $id, 'profile_extra.value' => $searchValue])
                ->addParams(['searchKeysCount' => $searchKeysCount + 1]);

        } elseif (is_array($searchValue) && count($searchValue) === 2 && $searchValue) {

            if (count($searchValue) == 2 && $searchValue[0] !== '' && $searchValue[1] !== '') {

                // both min and max
                $query->orWhere("profile_extra.field_id = $id and profile_extra.value >= :$minAttribute and profile_extra.value <= :$maxAttribute", [
                    $minAttribute => $searchValue[0], $maxAttribute => $searchValue[1],
                ])->addParams(['searchKeysCount' => $searchKeysCount + 1]);

            } else if ($searchValue[0] !== '') {

                // min only
                $query->orWhere("profile_extra.field_id = $id and profile_extra.value >= :$minAttribute", [
                    $minAttribute => $searchValue[0],
                ])->addParams(['searchKeysCount' => $searchKeysCount + 1]);

            } else if ($searchValue[1] !== '') {

                // max only
                $query->orWhere("profile_extra.field_id = $id and profile_extra.value <= :$maxAttribute", [
                    $maxAttribute => $searchValue[1],
                ])->addParams(['searchKeysCount' => $searchKeysCount + 1]);

            }
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

        return Html::encode(sprintf("%s %s %s", $this->prefix, $value, $this->postfix));
    }
}
