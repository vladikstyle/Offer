<?php

namespace app\models\fields;

use app\base\Model;
use app\forms\ProfileExtraForm;
use app\forms\UserSearchForm;
use app\helpers\Html;
use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\fields
 */
class MultiSelect extends BaseType
{
    const OUTPUT_COMMA_SEPARATED = 'outputCommaSeparated';
    const OUTPUT_CUSTOM = 'outputCustom';

    /**
     * @var string
     */
    public $options;

    /**
     * @var integer
     */
    public $outputType;
    /**
     * @var string
     */
    public $outputFormat;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['options'], 'safe'],
            [['outputType'], 'default', 'value' => self::OUTPUT_COMMA_SEPARATED],
            [['outputType', 'options'], 'required'],
            [['outputType'], 'in', 'range' => [self::OUTPUT_COMMA_SEPARATED, self::OUTPUT_CUSTOM]],
            [['outputFormat'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return array_merge(parent::getFieldOptions(), [
            'options' => [
                'type' => 'textarea',
                'label' => Yii::t('app', 'Values'),
                'hint' => Yii::t('app', 'Every {0} on each line', '<code>value => title</code>'),
            ],
            'outputType' => [
                'type' => 'dropdown',
                'items' => [
                    self::OUTPUT_COMMA_SEPARATED => Yii::t('app', 'Comma separated'),
                    self::OUTPUT_CUSTOM => Yii::t('app', 'Custom format'),
                ],
            ],
            'outputFormat' => [
                'type' => 'textarea',
                'label' => Yii::t('app', 'Custom format'),
                'hint' => Yii::t('app', 'Use {key} and {value} as placeholders'),
            ]
        ]);
    }

    /**
     * @return array
     */
    public function getFieldRules()
    {
        $rules = [];

        // form and search

        $rules[$this->profileField->alias . '.safe'] = [
            $this->profileField->alias, 'each', 'rule' => ['safe'],
            'on' => [Model::SCENARIO_FORM, Model::SCENARIO_SEARCH],
        ];

        return array_merge(parent::getFieldRules(), $rules);
    }

    /**
     * @param ActiveForm $form
     * @param ProfileExtraForm $model
     * @param array $options
     * @return mixed
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

        return $form->field($model, $this->profileField->alias)->listBox($this->getSelectItems(), $options);
    }

    /**
     * @return array
     */
    public function getSelectItems()
    {
        $items = [];

        foreach (explode("\n", $this->options) as $option) {
            if (strpos($option, '=>') !== false) {
                list($key, $value) = explode('=>', $option);
                $items[trim($key)] = Yii::t($this->profileField->language_category, trim($value));
            } else {
                $items[] = Yii::t($this->profileField->language_category, trim($option));
            }
        }

        return $items;
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
        $options['prompt'] = '';

        if (!$userHasPremium && $this->profileField->searchable_premium == true) {
            $options = array_merge($options, [
                'disabled' => 'disabled',
                'prompt' => Yii::t('app', 'Premium only'),
                'rel' => 'tooltip',
                'title' => Yii::t('app', 'Activate premium account to use this search criteria'),
            ]);
        }

        $formName = "{$searchAttribute}[{$this->profileField->id}]";

        return $form->field($model, $formName)->dropDownList($this->getSelectItems(), $options)
            ->label($this->profileField->getFieldTitle());
    }

    /**
     * @param \yii\db\Query $query
     * @param string $searchValue
     * @throws \Exception
     */
    public function applySearchQuery($query, $searchValue)
    {
        $searchKeysCount = ArrayHelper::getValue($query->params, 'searchKeysCount', 0);
        if (!is_array($searchValue)) {
            $searchValue = [$searchValue];
        }

        if (count($searchValue) > 10) {
            $searchValue = array_slice($searchValue, 0, 10);
        }

        $index = 0;
        $conditions = [];
        foreach ($searchValue as $value) {
            $searchAttribute = $this->prepareQueryParam('Multiple' . $index++);
            $conditions[] = "json_search(profile_extra.value, 'one', :$searchAttribute) > 0";
            $query->addParams([$searchAttribute => $value]);
        }

        $query->orWhere(['and',
            ['profile_extra.field_id' => $this->profileField->id],
            'json_valid(profile_extra.value) and (' . implode(' and ', $conditions) . ')',
        ])->addParams(['searchKeysCount' => $searchKeysCount + 1]);
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

        try {
            $value = json_decode($value, true);
        } catch (\Exception $exception) {
            Yii::error($exception->getMessage());
            return null;
        }

        $items = $this->getSelectItems();
        $output = [];
        $customOutput = '';

        if (is_array($value)) {
            foreach ($value as $key) {
                if (isset($items[$key])) {
                    $output[$key] = $items[$key];
                    if ($this->outputType === self::OUTPUT_CUSTOM) {
                        $customOutput .= strtr($this->outputFormat, [
                            '{key}' => $key,
                            '{value}' => $items[$key],
                        ]);
                    }
                }
            }
        }

        if ($this->outputType === self::OUTPUT_COMMA_SEPARATED) {
            return count($output) ? Html::encode(implode(', ', $output)) : null;
        } else {
            return $customOutput;
        }
    }
}
