<?php

namespace app\modules\api\components;

use app\modules\api\filters\HttpsFilter;
use app\modules\api\filters\VerbFilter;
use app\traits\RequestResponseTrait;
use Yii;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\components
 */
class Controller extends \yii\rest\Controller
{
    use RequestResponseTrait;

    public $layout = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['httpsOnly']['class'] = HttpsFilter::class;
        $behaviors['verbFilter']['class'] = VerbFilter::class;
        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
            'text/html' => Response::FORMAT_JSON,
        ];

        return $behaviors;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        Yii::$app->response->headers['Access-Control-Allow-Origin'] = '*';

        return parent::beforeAction($action);
    }
}
