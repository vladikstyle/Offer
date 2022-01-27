<?php

namespace app\modules\admin\helpers;

use app\models\Order;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\helpers
 */
class Html extends \yii\helpers\Html
{
    /**
     * @param $method
     * @return string
     */
    public static function paymentMethodToIcon($method)
    {
        switch ($method) {
            case Order::PAYMENT_METHOD_STRIPE:
                $icon = 'stripe';
                break;
            case Order::PAYMENT_METHOD_PAYPAL:
                $icon = 'paypal';
                break;
            case Order::PAYMENT_METHOD_ROBOKASSA:
                $icon = 'robokassa';
                break;
            default:
                $icon = 'generic';
                break;
        }

        return Html::tag('i', '', ['class' => 'payment-icon payment-icon-' . $icon]);
    }

    /**
     * @param $status
     * @param string $defaultClass
     * @return string
     */
    public static function paymentStatusToLabel($status, $defaultClass = 'label ')
    {
        switch ($status) {
            case Order::STATUS_NEW:
                $class = 'label-info';
                $statusText = Yii::t('app', 'New');
                break;
            case Order::STATUS_IN_PROGRESS:
                $class = 'label-primary';
                $statusText = Yii::t('app', 'In progress');
                break;
            case Order::STATUS_COMPLETED:
                $class = 'label-success';
                $statusText = Yii::t('app', 'Completed');
                break;
            case Order::STATUS_CANCELLED:
                $class = 'label-danger';
                $statusText = Yii::t('app', 'Cancelled');
                break;
            default:
                $class = 'label-default';
                $statusText = $status;
                break;
        }

        return Html::tag('span', $statusText, ['class' => $defaultClass . $class]);
    }

    /**
     * @param $permissions
     * @return array[]
     */
    public static function getSelectedPermissions($permissions)
    {
        $options = ['options' => []];
        foreach ($permissions as $permission) {
            $options['options'][$permission] = ['selected' => true];
        }

        return $options;
    }
}
