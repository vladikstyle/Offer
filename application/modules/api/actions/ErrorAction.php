<?php

namespace app\modules\api\actions;

use app\modules\api\components\ApiResult;
use app\modules\api\components\ErrorCode;
use Yii;
use yii\base\Action;
use app\modules\api\components\ApiException;
use yii\base\UserException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\actions
 */
class ErrorAction extends Action
{
    /**
     * @var string
     */
    public $defaultName;
    /**
     * @var string
     */
    public $defaultMessage;

    /**
     * @return ApiResult|object|string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            return '';
        }

        if ($exception instanceof ApiException) {
            $statusCode = $exception->statusCode;
            $apiCode = $exception->apiCode;
        } else {
            $statusCode = $exception->getCode();
            $apiCode = ErrorCode::UNKNOWN_ERROR;
        }

        $name = $exception->getName();
        if ($statusCode) {
            $name .= " (#$statusCode)";
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = $this->defaultMessage ?: Yii::t('yii', 'An internal server error occurred.');
        }

        return ApiResult::create()
            ->successful(false)
            ->withApiCode($apiCode)
            ->withMessage($message)
            ->withData(['name' => $name, 'type' => get_class($exception)]);
    }
}
