<?php

namespace app\payments;

use app\models\User;
use app\traits\SettingsTrait;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\payments
 */
class CheckoutHelper
{
    /**
     * @param $user
     * @param $credits
     * @return StripeCheckout
     * @throws \Exception
     */
    public static function getStripe($user, $credits = null)
    {
        $settings = Yii::$app->settings;

        $checkout = new StripeCheckout();
        $checkout->setUser($user);
        $checkout->setCurrency($settings->get('common', 'paymentCurrency'));
        $checkout->setSecretKey($settings->get('common', 'paymentStripeSecretKey'));
        $checkout->setWebhookSecret($settings->get('common', 'paymentStripeWebhookSecret'));
        if ($credits !== null) {
            $checkout->setCredits($credits);
        }

        $checkout->configureStripe();

        return $checkout;
    }

    /**
     * @param User $user
     * @param null $credits
     * @return PaypalCheckout
     * @throws \Exception
     */
    public static function getPaypal($user, $credits = null)
    {
        $settings = Yii::$app->settings;

        $checkout = new PaypalCheckout();
        $checkout->setUser($user);
        $checkout->setCurrency($settings->get('common', 'paymentCurrency'));
        $checkout->setClientId($settings->get('common', 'paymentPaypalClientId'));
        $checkout->setClientSecret($settings->get('common', 'paymentPaypalClientSecret'));
        $checkout->setMode( (bool) $settings->get('common', 'paymentPaypalSandbox')
            ? PaypalCheckout::MODE_SANDBOX : PaypalCheckout::MODE_LIVE);
        if ($credits !== null) {
            $checkout->setCredits($credits);
        }

        return $checkout;
    }

    /**
     * @param User $user
     * @param $credits
     * @return RobokassaCheckout
     * @throws \Exception
     */
    public static function getRobokassa($user, $credits = null)
    {
        $settings = Yii::$app->settings;
        $currency = $settings->get('common', 'paymentCurrency');

        $checkout = new RobokassaCheckout();
        $checkout->setUser($user);
        $checkout->setCurrency($currency);
        if ($currency != 'RUB') {
            $checkout->setOutSumCurrency($currency);
        }
        if ($credits !== null) {
            $checkout->setCredits($credits);
        }
        $checkout->setIsTest($settings->get('common', 'robokassaTestMode'));
        $checkout->setMerchantLogin($settings->get('common', 'robokassaMerchantLogin'));
        $checkout->setPassword1($settings->get('common', 'robokassaMerchantPassword1'));
        $checkout->setPassword2($settings->get('common', 'robokassaMerchantPassword2'));
        $checkout->setHashAlgorithm($settings->get('common', 'robokassaHashing'));

        return $checkout;
    }
}
