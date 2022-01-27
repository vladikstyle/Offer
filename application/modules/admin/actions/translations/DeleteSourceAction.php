<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use app\modules\admin\controllers\LanguageController;
use app\models\LanguageSource;
use Yii;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 * @property LanguageController $controller
 */
class DeleteSourceAction extends Action
{
    /**
     * @return array
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ids = $this->controller->request->post('ids');

        LanguageSource::deleteAll(['id' => (array) $ids]);

        return [];
    }
}
