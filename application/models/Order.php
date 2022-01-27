<?php

namespace app\models;

use app\behaviors\GuidBehavior;
use app\models\query\OrderQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property string $guid
 * @property int $user_id
 * @property string $currency
 * @property float $total_price
 * @property integer $amount
 * @property string $status
 * @property string $payment_method
 * @property string $payment_id
 * @property string|array $data
 * @property int $created_at
 * @property int $updated_at
 * @property int $callback_at
 *
 * @property User $user
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'NEW';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    const PAYMENT_METHOD_STRIPE = 'stripe';
    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_ROBOKASSA = 'robokassa';

    public static $statuses = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public static $paymentMethods = [
        self::PAYMENT_METHOD_STRIPE,
        self::PAYMENT_METHOD_PAYPAL,
        self::PAYMENT_METHOD_ROBOKASSA,
    ];

    /**
     * @var array
     */
    public $orderData = [];

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @return OrderQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'guid' => [
                'class' => GuidBehavior::class,
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at', 'callback_at', 'amount'], 'integer'],
            [['payment_id'], 'string', 'max' => 255],
            [['payment_method'], 'string', 'max' => 32],
            [['status'], 'in', 'range' => self::$statuses],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [['guid'], 'unique'],
            [['guid'], 'string', 'max' => 36, 'min' => 36],
            ['currency', 'string', 'min' => 3, 'max' => 3],
            ['data', 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'guid' => Yii::t('app', 'GUID'),
            'user_id' => Yii::t('app', 'User'),
            'currency' => Yii::t('app', 'Currency'),
            'total_price' => Yii::t('app', 'Total Price'),
            'amount' => Yii::t('app', 'Amount'),
            'payment_method' => Yii::t('app', 'Payment Method'),
            'payment_id' => Yii::t('app', 'Payment ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'callback_at' => Yii::t('app', 'Callback At'),
            'data' => Yii::t('app', 'Data'),
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        if (!empty($this->data)) {
            try {
                $this->orderData = json_decode($this->data, true);
            } catch (\Exception $e) {
                Yii::warning('Invalid order json data');
            }
        }
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (isset($this->orderData)) {
            $this->data = json_encode($this->orderData);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param $key
     * @param $value
     */
    public function setData($key, $value)
    {
        ArrayHelper::setValue($this->orderData, $key, $value);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param $status
     * @param bool $save
     * @throws Exception
     */
    public function updateStatus($status, $save = true)
    {
        $this->status = $status;
        if ($save) {
            if (!$this->save()) {
                throw new Exception('Could not update order status');
            }
        }
    }

    /**
     * @param $paymentId
     * @param bool $save
     * @throws Exception
     */
    public function updatePaymentId($paymentId, $save = true)
    {
        $this->payment_id = (string) $paymentId;
        if ($save) {
            if (!$this->save()) {
                throw new Exception('Could not update payment id');
            }
        }
    }

    /**
     * @return array
     */
    public function getPaymentMethodsLabels()
    {
        return [
            self::PAYMENT_METHOD_STRIPE => 'Stripe',
            self::PAYMENT_METHOD_PAYPAL => 'PayPal',
            self::PAYMENT_METHOD_ROBOKASSA => 'Robokassa',
        ];
    }
}
