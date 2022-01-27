<?php

namespace app\payments;

use app\models\Order;
use app\models\Price;
use app\models\User;
use app\traits\managers\BalanceManagerTrait;
use app\traits\RequestResponseTrait;
use app\traits\SessionTrait;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\payments
 */
class Checkout extends Component
{
    use RequestResponseTrait, SessionTrait, BalanceManagerTrait;

    /**
     * @var User
     */
    protected $user;
    /**
     * @var integer
     */
    protected $credits;
    /**
     * @var float
     */
    protected $amount;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $paymentMethod = 'generic';

    /**
     * @param $user User
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $credits
     * @throws Exception
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;
        $this->amount = $this->getAmountForCredits($credits);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param $paymentId
     * @param $transactionData
     * @return void
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function successPayment($paymentId, $transactionData)
    {
        $order = $this->findOrder($paymentId);
        $transactionData['orderId'] = $order->id;
        $this->balanceManager->increase(['user_id' => $this->user->id], $order->amount, $transactionData);
        $this->session->setFlash('success',
            Yii::t('app', 'Added {0} credits to your balance', $order->amount)
        );

        return $order->updateStatus(Order::STATUS_COMPLETED);
    }

    /**
     * @param $paymentId
     * @return void
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function cancelPayment($paymentId)
    {
        $this->session->setFlash('warning', Yii::t('app', 'Payment canceled'));

        return $this->updatePaymentStatus($paymentId, Order::STATUS_CANCELLED);
    }

    /**
     * @param $paymentId
     * @param $status
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function updatePaymentStatus($paymentId, $status)
    {
        $order = $this->findOrder($paymentId);

        return $order->updateStatus($status);
    }

    /**
     * @param null $paymentId
     * @return Order
     * @throws \yii\db\Exception
     */
    public function createOrder($paymentId = null)
    {
        $order = new Order();
        $order->user_id = $this->user->id;
        $order->currency = $this->currency;
        $order->total_price = $this->amount;
        $order->amount = $this->credits;
        $order->payment_method = $this->paymentMethod;
        $order->status = Order::STATUS_NEW;
        $order->payment_method = $this->paymentMethod;
        if ($paymentId !== null) {
            $order->payment_id = $paymentId;
        }
        if (!$order->save()) {
            throw new \yii\db\Exception('Could not create order entry');
        }

        return $order;
    }

    /**
     * @param $paymentId
     * @param User|null $user
     * @return Order|array|\yii\db\ActiveRecord|null
     * @throws NotFoundHttpException
     */
    protected function findOrder($paymentId, $user = null)
    {
        $query = Order::find()
            ->where(['payment_method' => $this->paymentMethod, 'payment_id' => $paymentId]);

        if ($user !== null) {
            $query = $query->andWhere(['user_id' => $user->id]) ;
        }

        $order = $query->one();
        if ($order === null) {
            throw new NotFoundHttpException('Order not found');
        }

        return $order;
    }

    /**
     * @param $credits
     * @return float|string
     * @throws Exception
     */
    protected function getAmountForCredits($credits)
    {
        /** @var Price $price */
        $price = Price::find()->where(['credits' => $credits])->one();
        if ($price === null) {
            throw new Exception("Could not find price for {$credits}");
        }

        return $price->getActualPrice();
    }
}
