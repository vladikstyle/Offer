<?php

namespace app\models\fields;

use app\base\Model;
use app\events\ProfileFieldEvent;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\fields
 */
class Text extends BaseType
{
    const VALIDATOR_EMAIL = 'email';
    const VALIDATOR_URL = 'url';

    /**
     * @var
     */
    public $validator;
    /**
     * @var
     */
    public $minLength;
    /**
     * @var int
     */
    public $maxLength = 255;
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
            [['validator'], 'in', 'range' => [self::VALIDATOR_EMAIL, self::VALIDATOR_URL]],
            [['maxLength', 'minLength'], 'integer', 'min' => 1, 'max' => 255],
            [['prefix', 'postfix'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return array_merge(parent::getFieldOptions(), [
            'validator' => [
                'type' => 'dropdown',
                'label' => Yii::t('app', 'Validator'),
                'items' => [
                    self::VALIDATOR_URL => Yii::t('app', 'URL'),
                    self::VALIDATOR_EMAIL => Yii::t('app', 'E-mail'),
                ],
            ],
            'minLength' => [
                'type' => 'text',
                'label' => Yii::t('app', 'Minimum length'),
            ],
            'maxLength' => [
                'type' => 'text',
                'label' => Yii::t('app', 'Maximum length'),
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

        // form and search

        if (empty($this->maxLength) || $this->maxLength > 255) {
            $rules[$this->profileField->alias . '.maxLength'] = [
                $this->profileField->alias,
                'string', 'max' => 255, 'on' => [Model::SCENARIO_FORM, Model::SCENARIO_SEARCH],
            ];
        } else {
            $rules[$this->profileField->alias . '.maxLength'] = [
                $this->profileField->alias,
                'string', 'max' => $this->maxLength, 'on' => [Model::SCENARIO_FORM, Model::SCENARIO_SEARCH],
            ];
        }

        if (!empty($this->minLength)) {
            $rules[$this->profileField->alias . '.minLength'] = [
                $this->profileField->alias, 'string', 'min' => $this->minLength, 'on' => [
                Model::SCENARIO_FORM, Model::SCENARIO_SEARCH,
            ]];
        }

        if (!empty($this->validator)) {
            $rules[$this->profileField->alias . '.validator'] = [
                $this->profileField->alias,
                $this->validator,
                'on' => [Model::SCENARIO_FORM, Model::SCENARIO_SEARCH],
            ];
        }

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
     * @param ActiveForm $form
     * @param $model
     * @param $searchAttribute
     * @param bool $userHasPremium
     * @param array $options
     * @return \yii\widgets\ActiveField
     */
    public function getFieldSearchInputs($form, $model, $searchAttribute, $userHasPremium = false, $options = [])
    {
        if (!$userHasPremium && $this->profileField->searchable_premium == true) {
            $options = array_merge($options, [
                'disabled' => 'disabled',
                'premium' => 'premium',
                'value' => '',
                'placeholder' => Yii::t('app', 'Premium only'),
                'rel' => 'tooltip',
                'title' => Yii::t('app', 'Activate premium account to use this search criteria'),
            ]);
        }

        $options['autocomplete'] = 'off';
        $formName = "{$searchAttribute}[{$this->profileField->id}]";

        return $form->field($model, $formName)->textInput($options)->label($this->profileField->getFieldTitle());
    }

    /**
     * @param \yii\db\Query $query
     * @param string $searchValue
     * @throws \Exception
     */
    public function applySearchQuery($query, $searchValue)
    {
        $searchKeysCount = ArrayHelper::getValue($query->params, 'searchKeysCount', 0);

        $query->orWhere(['and',
            ['profile_extra.field_id' => $this->profileField->id],
            ['like', 'profile_extra.value', $searchValue]
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

        if (empty($value)) {
            return null;
        }

        if ($this->validator == self::VALIDATOR_URL) {
            return Html::a($value, $value);
        }

        if ($this->validator == self::VALIDATOR_EMAIL) {
            return Html::mailto($value, $value);
        }

        return Html::encode(sprintf("%s %s %s", $this->prefix, $value, $this->postfix));
    }
}
