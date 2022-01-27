<?php

namespace app\modules\api\filters;

use app\modules\api\components\ApiResult;
use app\modules\api\components\ErrorCode;
use Yii;
use yii\base\ActionFilter;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\filters
 */
class HttpsFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        if (!Yii::$app->request->isSecureConnection && !YII_DEBUG) {
            $response = Yii::$app->response;
            $response->data = ApiResult::create()
                ->successful(false)
                ->withMessage('API works only via secured connection.')
                ->withApiCode(ErrorCode::HTTPS_ONLY);
            $response->send();

            return false;
        }

        return true;
    }
}
