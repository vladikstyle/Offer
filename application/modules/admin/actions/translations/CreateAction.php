<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use app\modules\admin\controllers\LanguageController;
use app\models\Language;
use Yii;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 * @property LanguageController $controller
 */
class CreateAction extends Action
{
    /**
     * @return array|string|\yii\web\Response
     */
    public function run()
    {
        $model = new Language();

        if ($this->controller->request->isAjax && $model->load($this->controller->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        } elseif ($model->load($this->controller->request->post()) && $model->save()) {
            return $this->controller->redirect(['view', 'id' => $model->language_id]);
        } else {
            return $this->controller->render('create', [
                'model' => $model,
            ]);
        }
    }
}
