<?php

namespace app\payments;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\payments
 */
class RobokassaTransaction extends TransactionInfo
{
    /**
     * @var array
     */
    public $robokassaData;

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_PAYMENT;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('app', 'Payment');
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return 'robokassa';
    }
}
