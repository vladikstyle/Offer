<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\models\Help;
use app\models\HelpCategory;
use app\models\Language;
use app\modules\admin\components\Permission;
use app\traits\AjaxValidationTrait;
use dosamigos\grid\actions\ToggleAction;
use kotchuprik\sortable\actions\Sorting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class HelpController extends \app\modules\admin\components\Controller
{
    use AjaxValidationTrait;

    /**
     * @var string
     */
    public $model = Help::class;

    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        return [
            'toggle' => [
                'class' => ToggleAction::class,
                'modelClass' => Help::class,
            ],
            'toggle-category' => [
                'class' => ToggleAction::class,
                'modelClass' => HelpCategory::class,
            ],
            'sorting' => [
                'class' => Sorting::class,
                'query' => Help::find(),
                'orderAttribute' => 'sort_order',
            ],
            'sorting-category' => [
                'class' => Sorting::class,
                'query' => HelpCategory::find(),
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
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::HELP,
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'delete-category' => ['post'],
                    'sorting' => ['post'],
                    'sorting-category' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Help::find()->joinWith(['helpCategory']),
            'sort'=> [
                'defaultOrder' => [
                    'sort_order' => SORT_ASC,
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCategories()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => HelpCategory::find(),
            'sort'=> [
                'defaultOrder' => [
                    'sort_order' => SORT_ASC,
                ],
            ],
        ]);

        return $this->render('categories', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $help = new Help();

        if ($help->load(Yii::$app->request->post()) && $help->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Help item has been created'));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'help' => $help,
            'helpCategories' => ArrayHelper::map(HelpCategory::find()->all(), 'id', 'title'),
            'languages' => Language::getLanguageNames(true, true),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreateCategory()
    {
        $helpCategory = new HelpCategory();

        if ($helpCategory->load(Yii::$app->request->post()) && $helpCategory->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Help category has been created'));
            return $this->redirect(['categories']);
        }

        return $this->render('create-category', [
            'helpCategory' => $helpCategory,
            'languages' => Language::getLanguageNames(true, true),
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        /** @var Help $help */
        $help = Help::find()->multilingual()->where(['help.id' => $id])->one();
        if ($help == null) {
            throw new NotFoundHttpException();
        }

        if ($help->load(Yii::$app->request->post()) && $help->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Help item has been updated'));
            return $this->redirect(['update', 'id' => $help->id]);
        }

        return $this->render('update', [
            'help' => $help,
            'helpCategories' => ArrayHelper::map(HelpCategory::find()->all(), 'id', 'title'),
            'languages' => Language::getLanguageNames(true, true),
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateCategory($id)
    {
        /** @var HelpCategory $helpCategory */
        $helpCategory = HelpCategory::find()->multilingual()->where(['help_category.id' => $id])->one();
        if ($helpCategory == null) {
            throw new NotFoundHttpException();
        }

        if ($helpCategory->load(Yii::$app->request->post()) && $helpCategory->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Help category has been updated'));
            return $this->redirect(['update-category', 'id' => $helpCategory->id]);
        }

        return $this->render('update-category', [
            'helpCategory' => $helpCategory,
            'languages' => Language::getLanguageNames(true, true),
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $help = Help::findOne(['id' => $id]);
        if ($help == null) {
            throw new NotFoundHttpException();
        }

        if ($help->delete()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Help item has been deleted'));
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDeleteCategory($id)
    {
        $helpCategory = HelpCategory::findOne(['id' => $id]);
        if ($helpCategory == null) {
            throw new NotFoundHttpException();
        }

        if ($helpCategory->delete()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Help category has been deleted'));
        }

        return $this->redirect(['categories']);
    }
}
