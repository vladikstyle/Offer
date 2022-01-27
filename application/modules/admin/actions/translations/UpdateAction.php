<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use app\modules\admin\controllers\LanguageController;
use Yii;
use yii\widgets\ActiveForm;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 * @property LanguageController $controller
 */
class UpdateAction extends Action
{
    /**
     * @param $id
     * @return array|string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function run($id)
    {
        $model = $this->controller->findModel(['language_id' => $id]);

        if ($this->controller->request->isAjax && $model->load($this->controller->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } elseif ($model->load($this->controller->request->post()) && $model->save()) {
            $this->controller->session->setFlash('success', Yii::t('app', 'Language has been updated'));
            return $this->controller->redirect(['update', 'id' => $model->language_id]);
        }

        return $this->controller->render('update', [
            'model' => $model,
        ]);
    }
}
