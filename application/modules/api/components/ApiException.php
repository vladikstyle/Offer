<?php

namespace app\modules\api\components;

use yii\base\UserException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\components
 */
class ApiException extends UserException
{
    /**
     * @var null|integer
     */
    public $apiCode;
    /**
     * @var null|integer
     */
    public $statusCode;

    /**
     * @param int $status
     * @param null $message
     * @param null $apiCode
     */
    public function __construct($status, $message = null, $apiCode = null)
    {
        $this->statusCode = $status;
        $this->apiCode = $apiCode;
        parent::__construct($message, $status);
    }
}
