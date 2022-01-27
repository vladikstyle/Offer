<?php

namespace app\models;

use app\traits\SettingsTrait;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property int $id
 * @property int $credits
 * @property float $base_price
 * @property string $discount
 * @property int $created_at
 * @property int $updated_at
 */
class Price extends \yii\db\ActiveRecord
{
    use SettingsTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%price}}';
    }

    /**
     * @return array|\string[][]
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credits', 'base_price'], 'required'],
            [['credits'], 'number', 'min' => 1, 'max' => 1000000],
            [['base_price'], 'number', 'min' => 0.01, 'max' => 1000000],
            [['created_at', 'updated_at'], 'integer'],
            [['discount'], 'string', 'max' => 255],
            [['discount'], 'match', 'pattern' => '/^[0-9]{0,}[.]?[0-9]{0,}(%)?$/i'],
            [['discount'], function () {
                if ($this->isDiscountPercentage()) {
                    $discount = (float) $this->discount;
                    if ($discount < 0 || $discount > 100) {
                        $this->addError('discount', Yii::t('app', 'Discount value is invalid'));
                        return false;
                    }
                    return true;
                }
                if ($this->discount >= $this->base_price) {
                    $this->addError('discount', Yii::t('app', 'Discount can not be greater than price'));
                    return false;
                } elseif ($this->discount < 0) {
                    $this->addError('discount', Yii::t('app', 'Discount must be greater than 0'));
                    return false;
                }
                return true;
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'credits' => Yii::t('app', 'Credits'),
            'base_price' => Yii::t('app', 'Base Price'),
            'discount' => Yii::t('app', 'Discount'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return bool
     */
    public function isDiscountPercentage()
    {
        if (!isset($this->discount)) {
            return false;
        }

        return substr($this->discount, -1) == '%';
    }

    /**
     * @param bool $format
     * @return float|string
     * @throws \Exception
     */
    public function getActualPrice($format = false)
    {
        $price = $this->base_price;

        if (!empty($this->discount)) {
            if ($this->isDiscountPercentage()) {
                $discount = str_replace('%', '', $this->discount);
                $price = $price - ($price * $discount * 0.01);
            } else {
                $price = $price - $this->discount;
            }
        }

        $price = (float)number_format($price, 2, '.', '');

        if ($format) {
            $currency = Currency::getCurrency($this->settings->get('common', 'paymentCurrency'));
            $price = sprintf($currency->format, $price);
        }

        return $price;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getPricePerCredit()
    {
        if (empty($this->credits) || $this->credits == 0) {
            return null;
        }

        return number_format($this->getActualPrice() / $this->credits, 2, '.', '');
    }
}
