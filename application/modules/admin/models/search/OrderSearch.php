<?php

namespace app\modules\admin\models\search;

use app\models\Order;
use app\models\User;
use yii\data\ActiveDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\model\search
 */
class OrderSearch extends \yii\base\Model
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var integer
     */
    public $user_id;
    /**
     * @var string
     */
    public $guid;
    /**
     * @var string
     */
    public $status;
    /**
     * @var string
     */
    public $payment_method;
    /**
     * @var integer
     */
    public $amount;
    /**
     * @var float
     */
    public $total_price;
    /**
     * @var Order
     */
    private $model;

    public function init()
    {
        parent::init();
        $this->model = new Order();
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['amount'], 'integer', 'min' => 1],
            [['guid'], 'string'],
            [['total_price'], 'number', 'min' => 0],
            [['status'], 'in', 'range' => Order::$statuses],
            [['payment_method'], 'in', 'range' => Order::$paymentMethods],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find()
            ->select([
                'order.*',
                'user.username',
            ])
            ->groupBy('order.id')
            ->joinWith(['user', 'user.profile']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere([
                'order.id' => $this->id,
                'order.status' => $this->status,
                'order.payment_method' => $this->payment_method,
                'order.amount' => $this->amount,
                'order.total_price' => $this->total_price,
            ])
            ->andFilterWhere(['or',
                ['like', 'order.guid', $this->guid],
            ]);

        if ($this->user_id) {
            $query->andFilterWhere(['order.user_id' => $this->user_id]);
        }

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getPaymentMethodsLabels()
    {
        return $this->model->getPaymentMethodsLabels();
    }

    /**
     * @return array|null
     */
    public function getUserSelection()
    {
        if (!isset($this->user_id)) {
            return null;
        }

        $user = User::findOne(['id' => $this->user_id]);

        return $user == null ? null : ['id' => $user->id, 'text' => $user->username];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
