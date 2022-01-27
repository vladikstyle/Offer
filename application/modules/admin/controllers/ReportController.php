<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\modules\admin\components\Permission;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\modules\admin\models\Report;
use dosamigos\grid\actions\ToggleAction;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class ReportController extends \app\modules\admin\components\Controller
{
    /**
     * @var string|Report
     */
    public $model = Report::class;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'toggle' => [
                'class' => ToggleAction::class,
                'modelClass' => $this->model,
                'scenario' => Report::SCENARIO_TOGGLE,
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::REPORTS,
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Reports index page
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Report::find()->latest();
        $query->with(['fromUser', 'reportedUser', 'fromUser.profile', 'reportedUser.profile']);
        $type = $this->request->get('type', Report::TYPE_NEW);

        if ($type == Report::TYPE_NEW) {
            $query = $query->newOnly();
        }

        return $this->render('index', [
            'type' => $type,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
            ]),
        ]);
    }

    /**
     * @param integer $id
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        /* @var $report Report */
        $report = $this->findModel(['id' => $id]);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$report->delete()) {
            throw new \Exception('Could not delete report entry');
        }

        $this->session->setFlash('success', Yii::t('app', 'Report has been removed'));
        return $this->redirect($this->request->referrer);
    }
}
