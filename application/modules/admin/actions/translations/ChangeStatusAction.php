<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use Yii;
use yii\web\Response;
use app\models\Language;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 */
class ChangeStatusAction extends Action
{
    /**
     * @return array
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $language = Language::findOne($this->controller->request->post('language_id', ''));
        if ($language !== null) {
            $language->status = $this->controller->request->post('status', Language::STATUS_BETA);
            if ($language->validate()) {
                $language->save();
            }
        }

        return $language->getErrors();
    }
}
