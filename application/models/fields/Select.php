<?php

namespace app\models\fields;

use app\base\Model;
use app\forms\UserSearchForm;
use app\helpers\Html;
use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\fields
 */
class Select extends BaseType
{
    /**
     * @var string
     */
    public $options;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['options'], 'safe'],
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
        ]);
    }

    /**
     * @return array
     */
    public function getFieldRules()
    {
        $rules = [];

        // form and search

        $rules[$this->profileField->alias . '.inRange'] = [
            $this->profileField->alias,
            'in', 'range' => array_keys($this->getSelectItems()),
            'on' => [Model::SCENARIO_FORM, Model::SCENARIO_SEARCH],
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
        $options['prompt'] = Yii::t('app', '-- Select --');
        return $form->field($model, $this->profileField->alias)->dropDownList($this->getSelectItems(), $options);
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

        $query->orWhere(['profile_extra.field_id' => $this->profileField->id, 'profile_extra.value' => $searchValue])
            ->addParams(['searchKeysCount' => $searchKeysCount + 1]);
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

        $items = $this->getSelectItems();

        return Html::encode(isset($items[$value]) ? $items[$value] : $value);
    }
}
