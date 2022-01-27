<?php

namespace app\payments;

use app\helpers\Url;
use app\models\Order;
use app\models\User;
use app\traits\managers\BalanceManagerTrait;
use app\traits\SettingsTrait;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\Environment;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * @package app\components
 *
 * @property string $clientId
 * @property string $clientSecret
 * @property string $mode
 * @property array $config
 */
class PaypalCheckout extends Checkout
{
    use SettingsTrait, BalanceManagerTrait;

    const MODE_SANDBOX = 'sandbox';
    const MODE_LIVE = 'live';

    /**
     * @var string
     */
    protected $paymentMethod = Order::PAYMENT_METHOD_PAYPAL;
    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;
    /**
     * @var string
     */
    public $mode = self::MODE_SANDBOX;
    /**
     * @var Environment
     */
    protected $environment;
    /**
     * @var PayPalHttpClient
     */
    protected $client;

    /**
     * @param $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @param $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function initializePaypal()
    {
        if ($this->mode === self::MODE_SANDBOX) {
            $this->environment = new SandboxEnvironment($this->clientId, $this->clientSecret);
        } else {
            $this->environment = new ProductionEnvironment($this->clientId, $this->clientSecret);
        }
        $this->client = new PayPalHttpClient($this->environment);
    }

    /**
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function checkout()
    {
        $this->initializePaypal();

        $order = $this->createOrder();

        $siteName = $this->settings->get('frontend', 'siteName');
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->guid,
                'description' => $siteName . ': ' . Yii::t('app', 'Credits purchase'),
                'amount' => [
                    'value' => $this->getAmount(),
                    'currency_code' => $this->currency,
                ]
            ]],
            'application_context' => [
                'cancel_url' =>  Url::to(['balance/paypal-failure'], true),
                'return_url' => Url::to(['balance/paypal-success'], true),
                'brand_name' => $siteName,
                'locale' => Yii::$app->language,
                'user_action' => 'PAY_NOW',
            ],
        ];

        try {
            $response = $this->client->execute($request);
            foreach ($response->result->links as $link) {
                if ($link->rel == 'approve') {
                    $order->updatePaymentId($response->result->id, false);
                    $order->updateStatus(Order::STATUS_IN_PROGRESS);
                    $this->response->redirect($link->href);
                    $this->response->send();
                    Yii::$app->end();
                }
            }
            $order->updateStatus(Order::STATUS_CANCELLED);
            $this->session->setFlash('danger', Yii::t('app', 'Unknown payment error occurred. Please try again later'));
        } catch (HttpException $e) {
            Yii::error(sprintf('HTTP %s: %s', $e->statusCode, $e->getMessage()));
            throw new \Exception('PayPal payment error');
        }
    }

    /**
     * @param $paymentId
     * @return bool
     * @throws \Exception
     */
    public function validatePayment($paymentId)
    {
        $this->initializePaypal();
        $order = $this->findOrder($paymentId);

        try {
            $request = new OrdersCaptureRequest($paymentId);
            $response = $this->client->execute($request);
            if ($response->result->status == Order::STATUS_COMPLETED) {
                $order->updateStatus(Order::STATUS_COMPLETED);
            } else {
                $order->updateStatus($response->result->status);
                $this->session->setFlash('error', 'Unexpected order status: ' . $order->status);
                return false;
            }

            $this->successPayment($paymentId, [
                'class' => PayPalTransaction::class,
                'paypalPaymentId' => $paymentId,
            ]);

            return true;

        } catch (HttpException $e) {
            Yii::error(sprintf('HTTP %s: %s', $e->statusCode, $e->getMessage()));
            $this->session->setFlash('error', 'Could not process payment - ' . $e->getMessage());
            return false;
        }
    }
}
