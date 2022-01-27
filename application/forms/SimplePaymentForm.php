<?php

namespace app\forms;

use yii\base\Model;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\forms
 */
class SimplePaymentForm extends Model
{
    /**
     * @var integer
     */
    public $credits;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['credits'], 'required'],
            ['credits', 'integer', 'min' => 1, 'max' => 10000],
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
