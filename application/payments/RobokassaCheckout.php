<?php

namespace app\payments;

use app\helpers\Common;
use app\models\Order;
use app\traits\managers\BalanceManagerTrait;
use app\traits\managers\UserManagerTrait;
use app\traits\SettingsTrait;
use Yii;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\payments
 */
class RobokassaCheckout extends Checkout
{
    use BalanceManagerTrait, SettingsTrait, UserManagerTrait;

    /**
     * @var string
     */
    protected $paymentMethod = Order::PAYMENT_METHOD_ROBOKASSA;
    /**
     * @var string
     */
    protected $merchantLogin;
    /**
     * @var string
     */
    protected $merchantPassword1;
    /**
     * @var string
     */
    protected $merchantPassword2;
    /**
     * @var boolean
     */
    protected $isTest = false;
    /**
     * @var string
     */
    protected $baseUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';
    /**
     * @var string
     */
    protected $hashAlgorithm = 'sha1';
    /**
     * @var string
     */
    protected $outSumCurrency = '';

    /**
     * @param $merchantLogin
     */
    public function setMerchantLogin($merchantLogin)
    {
        $this->merchantLogin = $merchantLogin;
    }

    /**
     * @param $merchantPassword1
     */
    public function setPassword1($merchantPassword1)
    {
        $this->merchantPassword1 = $merchantPassword1;
    }

    /**
     * @param $merchantPassword2
     */
    public function setPassword2($merchantPassword2)
    {
        $this->merchantPassword2 = $merchantPassword2;
    }

    /**
     * @param $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param $isTest
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;
    }

    /**
     * @param $hashAlgorithm
     */
    public function setHashAlgorithm($hashAlgorithm)
    {
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @param $outSumCurrency
     */
    public function setOutSumCurrency($outSumCurrency)
    {
        $this->outSumCurrency = $outSumCurrency;
    }

    /**
     * @return mixed|\yii\web\Response
     * @throws Exception
     */
    public function checkout()
    {
        $order = $this->createOrder();
        $order->updateStatus(Order::STATUS_IN_PROGRESS, false);
        $order->updatePaymentId($order->id);

        $shp = ['Shp_credits' => $this->credits, 'Shp_userId' => $this->user->id];
        $signature = $this->generateSignature($this->getAmount(), $order->id, $shp);

        $params = [
            'InvId' => $order->id,
            'MerchantLogin' => $this->merchantLogin,
            'OutSum' => $this->getAmount(),
            'Description' => Yii::t('youdate', 'Credits replenishment'),
            'SignatureValue' => $signature,
            'Culture' => Common::getShortLanguage(Yii::$app->language),
            'Encoding' => 'utf-8',
            'Email' => $this->user->email,
            'IsTest' => $this->isTest ? 1 : null,
        ];

        if (!empty($this->outSumCurrency)) {
            $params['OutSumCurrency'] = $this->outSumCurrency;
        }

        $url = $this->baseUrl;
        $url .= '?' . http_build_query($params);

        if (!empty($shp) && ($query = http_build_query($shp)) !== '') {
            $url .= '&' . $query;
        }

        return $this->response->redirect($url);
    }

    /**
     * @param $data
     * @return bool
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function validatePayment($data)
    {
        if (!isset($data['OutSum'], $data['InvId'], $data['SignatureValue'], $data['Shp_userId'], $data['Shp_credits'])) {
            throw new BadRequestHttpException();
        }

        $shp = [];
        foreach ($data as $key => $param) {
            if (strpos(strtolower($key), 'shp') === 0) {
                $shp[$key] = $param;
            }
        }

        if (!$this->checkSignature($data['SignatureValue'], $data['OutSum'], $data['InvId'], $this->merchantPassword1, $shp)) {
            throw new BadRequestHttpException('Could not validate Robokassa request');
        }

        $order = $this->findOrder($data['InvId']);
        if ($order === null) {
            throw new Exception("Could not find order #{$data['InvId']} (Robokassa payment)");
        }
        $this->user = $this->userManager->getUserById($data['Shp_userId']);
        if ($this->user === null) {
            throw new Exception('Could not find user (Robokassa payment)');
        }

        $this->successPayment($order->id, [
            'class' => RobokassaTransaction::class,
            'robokassaData' => $data,
        ]);

        return true;
    }

    /**
     * @param $sSignatureValue
     * @param $nOutSum
     * @param $nInvId
     * @param $sMerchantPass
     * @param array $shp
     * @return bool
     */
    public function checkSignature($sSignatureValue, $nOutSum, $nInvId, $sMerchantPass, $shp = [])
    {
        $signature = "{$nOutSum}:{$nInvId}:{$sMerchantPass}";

        if (!empty($shp)) {
            $signature .= ':' . $this->implodeShp($shp);
        }

        return strtolower($this->encryptSignature($signature)) === strtolower($sSignatureValue);
    }

    /**
     * @param $nOutSum
     * @param $nInvId
     * @param array $shp
     * @return string
     */
    protected function generateSignature($nOutSum, $nInvId, $shp = [])
    {
        if ($nInvId === null) {
            if ($this->outSumCurrency) {
                // MerchantLogin:OutSum::OutSumCurrency:Password#1
                $signature = "{$this->merchantLogin}:{$nOutSum}::{$this->outSumCurrency}:{$this->merchantPassword1}";
            } else {
                // MerchantLogin:OutSum::Password#1
                $signature = "{$this->merchantLogin}:{$nOutSum}::{$this->merchantPassword1}";
            }
        } else {
            if ($this->outSumCurrency) {
                // MerchantLogin:OutSum:InvId:OutSumCurrency:Password#1
                $signature = "{$this->merchantLogin}:{$nOutSum}:{$nInvId}:{$this->outSumCurrency}:{$this->merchantPassword1}";
            } else {
                // MerchantLogin:OutSum:InvId:Password#1
                $signature = "{$this->merchantLogin}:{$nOutSum}:{$nInvId}:{$this->merchantPassword1}";
            }
        }

        if (!empty($shp)) {
            $signature .= ':' . $this->implodeShp($shp);
        }

        return strtolower($this->encryptSignature($signature));
    }

    /**
     * @param $signature
     * @return string
     */
    protected function encryptSignature($signature)
    {
        return hash($this->hashAlgorithm, $signature);
    }

    /**
     * @param $shp
     * @return string
     */
    protected function implodeShp($shp)
    {
        ksort($shp);

        foreach ($shp as $key => $value) {
            $shp[$key] = $key . '=' . $value;
        }

        return implode(':', $shp);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getSiteCurrency()
    {
        return $this->settings->get('common', 'paymentCurrency', 'USD');
    }
}
