<?php

namespace app\controllers;

use app\models\Report;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class ReportController extends \app\base\Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function actionCreate()
    {
        $model = new Report();
        $model->from_user_id = Yii::$app->user->id;
        $model->reported_user_id = $this->request->post('reportedUserId');
        $model->reason = $this->request->post('reason');
        $model->description = $this->request->post('description');

        if ($model->save()) {
            return $this->sendJson([
                'success' => true,
                'message' => Yii::t('app', 'User has been reported'),
            ]);
        }

        return $this->sendJson([
            'success' => false,
            'message' => Yii::t('app', 'Could not create report for this user'),
            'errors' => $model->errors,
        ]);
    }
}
