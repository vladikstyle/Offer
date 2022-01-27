<?php

namespace app\modules\api\components;

use Yii;
use yii\base\BaseObject;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\components
 */
class ApiResult extends BaseObject
{
    /**
     * @var bool
     */
    public $success = true;
    /**
     * @var integer
     */
    public $apiCode = ErrorCode::NONE;
    /**
     * @var string
     */
    public $message = null;
    /**
     * @var array
     */
    public $data = [];

    /**
     * @return object|ApiResult
     * @throws \yii\base\InvalidConfigException
     */
    public static function create()
    {
        return Yii::createObject(__CLASS__);
    }

    /**
     * @param $success
     * @return $this
     */
    public function successful($success = true)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @param $apiCode
     * @return $this
     */
    public function withApiCode($apiCode)
    {
        $this->apiCode = $apiCode;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function withMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function withData($data = [])
    {
        $this->data = $data;
        return $this;
    }
}
