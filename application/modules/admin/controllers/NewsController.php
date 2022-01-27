<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\models\News;
use app\modules\admin\components\Permission;
use app\modules\admin\models\search\NewsSearch;
use app\traits\AjaxValidationTrait;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class NewsController extends \app\modules\admin\components\Controller
{
    use AjaxValidationTrait;

    /**
     * @var string
     */
    public $model = News::class;
    /**
     * @var string
     */
    public $layout = '@app/modules/admin/views/news/_layout.php';

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::NEWS,
            ],
        ]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        /** @var NewsSearch $searchModel */
        $searchModel = Yii::createObject(NewsSearch::class);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'newsDataProvider' => $searchModel->search($this->request->get()),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\ExitException
     */
    public function actionCreate()
    {
        /** @var News $newsModel */
        $newsModel = Yii::createObject(News::class);
        $newsModel->user_id = $this->getCurrentUser()->id;

        $this->performAjaxValidation($newsModel);

        if ($newsModel->load($this->request->post()) && $newsModel->save()) {
            $this->session->setFlash('success', Yii::t('app', 'News page has been created'));
            return $this->redirect(['update', 'id' => $newsModel->id]);
        }

        return $this->render('create', [
            'newsModel' => $newsModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionUpdate($id)
    {
        $newsModel = $this->findModel($id);

        $this->performAjaxValidation($newsModel);

        if ($newsModel->load($this->request->post()) && $newsModel->save()) {
            $this->session->setFlash('success', Yii::t('app', 'News page has been updated'));
            return $this->refresh();
        }

        return $this->render('update', [
            'newsModel' => $newsModel,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel(['id' => $id]);
        if ($model->delete()) {
            $this->session->setFlash('success', Yii::t('app', 'Log message has been deleted'));
        }

        return $this->redirect($this->request->referrer ?? 'index');
    }
}
