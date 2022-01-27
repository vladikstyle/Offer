<?php

namespace app\modules\api;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api
 */
class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    public function init()
    {
        parent::init();
        Yii::$app->user->enableSession = false;
        Yii::$app->request->enableCsrfCookie = false;
        Yii::$app->errorHandler->errorAction = $this->id . '/default/error';
    }
}
