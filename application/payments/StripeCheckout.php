<?php

namespace app\payments;

use app\helpers\Url;
use app\models\CardObject;
use app\models\Order;
use app\models\PaymentCustomer;
use app\traits\CacheTrait;
use Stripe\Event as StripeEvent;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Yii;
use yii\base\Exception;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\payments
 */
class StripeCheckout extends Checkout
{
    use CacheTrait;

    const STRIPE_API_VERSION = '2020-03-02';
    const CACHE_STRIPE_WEBHOOK_ALLOWED_IPS = 'stripeWebhookAllowedIps';

    /**
     * @var string
     */
    protected $paymentMethod = Order::PAYMENT_METHOD_STRIPE;
    /**
     * @var string
     */
    protected $stripeSecretKey;
    /**
     * @var string
     */
    protected $stripeToken;
    /**
     * @var string
     */
    protected $webhookSecret;
    /**
     * @var string
     */
    public $webhookIpAddresses = 'https://stripe.com/files/ips/ips_webhooks.json';
    /**
     * @var string
     */
    protected $service = 'stripe';

    /**
     * @param $key
     */
    public function setSecretKey($key)
    {
        $this->stripeSecretKey = $key;
    }

    /**
     * @param $webhookSecret
     */
    public function setWebhookSecret($webhookSecret)
    {
        $this->webhookSecret = $webhookSecret;
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->stripeToken = $token;
    }

    public function configureStripe()
    {
        Stripe::setApiVersion(self::STRIPE_API_VERSION);
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * @return Session
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \yii\db\Exception
     */
    public function createSession()
    {
        $order = $this->createOrder();

        $session = Session::create([
            'payment_method_types' => [
                'card',
            ],
            'line_items' => [[
                'name' => Yii::t('app', 'Credits') . ': ' . $this->credits,
                'quantity' => 1,
                'currency' => $this->currency,
                'amount' => $this->getAmount(),
            ]],
            'mode' => 'payment',
            'customer' => $this->getCustomerId(),
            'success_url' => Url::to('balance/stripe-success?sessionId={CHECKOUT_SESSION_ID}', true),
            'cancel_url' => Url::to(['balance/stripe-cancel'], true),
            'metadata' => [
                'orderId' => $order->id,
                'orderGuid' => $order->guid,
                'userId' => $this->user->id,
            ],
        ]);

        $order->payment_id = $session->payment_intent;
        $order->updateStatus(Order::STATUS_IN_PROGRESS);

        return $session;
    }

    /**
     * @param $sessionId
     * @return bool
     */
    public function validatePayment($sessionId)
    {
        try {
            $session = Session::retrieve($sessionId);
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
            $paymentId = $session->payment_intent;
            if ($paymentIntent->status == PaymentIntent::STATUS_SUCCEEDED) {
                $this->successPayment($paymentId, [
                    'class' => StripeTransaction::class,
                    'paymentIntent' => $paymentIntent,
                ]);
                return true;
            } elseif ($paymentIntent->status == PaymentIntent::STATUS_CANCELED) {
                $this->cancelPayment($paymentId);
                $this->session->setFlash('warning', Yii::t('app', 'Payment canceled'));
            } else {
                $this->updatePaymentStatus($paymentId, Order::STATUS_IN_PROGRESS);
            }
        } catch (\Exception $e) {
            Yii::error('Stripe payment validation: ' . $e->getMessage());
            $this->session->setFlash('error', 'Could not process payment - ' . $e->getMessage());
        }

        return false;
    }

    /**
     * @param $payload
     * @return bool
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function handleWebhook($payload)
    {
        if (!$this->validateIpAddress()) {
            if (YII_DEBUG) {
                Yii::warning('Webhook: invalid IP address ' . $this->request->userIP);
            }
            return false;
        }

        try {
            $signatureHeader = $this->request->headers['Stripe-Signature'];
            $event = \Stripe\Webhook::constructEvent(
                $payload, $signatureHeader, $this->webhookSecret
            );
        } catch(\UnexpectedValueException $e) {
            if (YII_DEBUG) {
                Yii::warning('Webhook: invalid payload');
            }
            return false;
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            if (YII_DEBUG) {
                Yii::warning('Webhook: invalid signature');
            }
            return false;
        } catch(\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }

        $intentEvents = [
            StripeEvent::PAYMENT_INTENT_SUCCEEDED,
            StripeEvent::PAYMENT_INTENT_CANCELED,
            StripeEvent::PAYMENT_INTENT_PAYMENT_FAILED,
        ];

        if (!in_array($event->type, $intentEvents)) {
            return true;
        }

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;
        $paymentId = $paymentIntent->id;

        $order = $this->findOrder(['order.payment_id' => $paymentIntent->id]);
        $this->user = $order->user;

        $callbackTs = $event->created;
        $callbackDt = new \DateTime('@' . $callbackTs);
        $order->setData(['callbacks', time()], [
            'datetime' => $callbackDt->format('Y-m-d H:i:s'),
            'raw' => $payload,
            'type' => $event->type
        ]);

        if (isset($order->callback_at)) {
            if ($callbackTs < $order->callback_at) {
                if (YII_DEBUG) {
                    Yii::warning('Got older callback, order #' . $order->id);
                }
                $order->save();
                return false;
            }
        } else {
            $order->callback_at = $callbackTs;
            $order->save();
        }

        switch ($event->type) {
            case StripeEvent::PAYMENT_INTENT_SUCCEEDED:
                if ($order->status !== Order::STATUS_COMPLETED) {
                    $this->successPayment($paymentId, [
                        'class' => StripeTransaction::class,
                        'paymentIntent' => $paymentIntent,
                    ]);
                }
                break;
            case StripeEvent::PAYMENT_INTENT_PAYMENT_FAILED:
            case StripeEvent::PAYMENT_INTENT_CANCELED:
                $this->cancelPayment($paymentId);
                break;
            default:
                // not implemented
                if (YII_DEBUG) {
                    Yii::warning('Webhook method not implemented yet: ' . $event->type);
                }
                return true;
        }

        return true;
    }

    /**
     * @return float|integer
     * @throws Exception
     */
    public function getAmount()
    {
        if (!$this->amount) {
            throw new Exception('Amount could not be null');
        }

        return $this->amount * 100;
    }

    /**
     * @return string
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function getCustomerId()
    {
        $paymentCustomer = PaymentCustomer::findOne(['user_id' => $this->user->id, 'service' => $this->service]);
        if ($paymentCustomer) {
            $stripeCustomerInfo = json_decode($paymentCustomer->data);
            if (sha1($this->stripeToken) !== $stripeCustomerInfo->stripeToken) {
                \Stripe\Customer::update($stripeCustomerInfo->id, ['source' => $this->stripeToken]);
            }
            return $stripeCustomerInfo->id;
        }

        $customer = \Stripe\Customer::create(['email' => $this->user->email, 'source' => $this->stripeToken]);
        $paymentCustomer = new PaymentCustomer();
        $paymentCustomer->user_id = $this->user->id;
        $paymentCustomer->service = $this->service;
        $paymentCustomer->data = json_encode(['id' => $customer->id, 'stripeToken' => sha1($this->stripeToken)]);
        $paymentCustomer->save();

        return $customer->id;
    }

    /**
     * @return bool
     */
    public function validateIpAddress()
    {
        $ipAddress = $this->request->userIP;

        $list = $this->cache->get(self::CACHE_STRIPE_WEBHOOK_ALLOWED_IPS);
        if ($list === false || YII_DEBUG) {
            $data = file_get_contents($this->webhookIpAddresses);
            $json = json_decode($data, true);
            $list = $json['WEBHOOKS'];
            $this->cache->set(self::CACHE_STRIPE_WEBHOOK_ALLOWED_IPS, $list, 86400);
        }

        if (YII_DEBUG) {
            $list[] = '127.0.0.1';
        }

        return in_array($ipAddress, $list);
    }
}
