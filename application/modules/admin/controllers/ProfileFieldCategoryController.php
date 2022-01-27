<?php

namespace app\modules\admin\controllers;

use app\models\ProfileField;
use app\models\ProfileFieldCategory;
use kotchuprik\sortable\actions\Sorting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Response;
use dosamigos\grid\actions\ToggleAction;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class ProfileFieldCategoryController extends \app\modules\admin\components\Controller
{
    /**
     * @var string
     */
    public $model = ProfileFieldCategory::class;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'toggle' => [
                'class' => ToggleAction::class,
                'modelClass' => $this->model,
            ],
            'sorting' => [
                'class' => Sorting::class,
                'query' => ProfileFieldCategory::find(),
                'orderAttribute' => 'sort_order',
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'sorting' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => ProfileFieldCategory::find(),
                'sort' => ['defaultOrder' => ['sort_order' => SORT_ASC]],
            ]),
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new ProfileFieldCategory();

        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Field category has been created'));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Field category has been updated'));
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
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
        /* @var $category ProfileFieldCategory */
        $category = $this->findModel(['id' => $id]);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$category->delete()) {
            throw new \Exception('Could not delete profile field category entry');
        }

        $this->session->setFlash('success', Yii::t('app', 'Field category has been removed'));
        return $this->redirect($this->request->referrer);
    }
}
