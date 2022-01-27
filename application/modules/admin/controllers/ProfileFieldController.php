<?php

namespace app\modules\admin\controllers;

use app\models\fields\BaseType;
use app\models\Help;
use app\models\ProfileFieldCategory;
use app\models\ProfileField;
use app\traits\AjaxValidationTrait;
use kotchuprik\sortable\actions\Sorting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use dosamigos\grid\actions\ToggleAction;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class ProfileFieldController extends \app\modules\admin\components\Controller
{
    use AjaxValidationTrait;

    /**
     * @var string
     */
    public $model = ProfileField::class;

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
                'query' => ProfileField::find(),
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
                'query' => ProfileField::find()->joinWith('category'),
                'sort' => ['defaultOrder' => ['sort_order' => SORT_ASC]],
            ]),
        ]);
    }

    /**
     * @return string|Response
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new ProfileField();
        $fieldClass = $this->getFieldClass($model);
        $fieldInstance = $this->getFieldInstance($fieldClass, $model);
        $model->setFieldInstance($fieldInstance);

        $this->performAjaxValidation($model);

        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Field type has been created'));
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => ProfileFieldCategory::find()->all(),
            'fieldClasses' => $this->getFieldClasses(),
            'fieldInstance' => $fieldInstance,
            'fieldClass' => $fieldClass,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\ExitException
     */
    public function actionUpdate($id)
    {
        /** @var ProfileField $model */
        $model = $this->findModel($id);
        $fieldClass = $this->getFieldClass($model);

        if ($fieldClass == $model->field_class) {
            $fieldInstance = $model->getFieldInstance();
        } else {
            $fieldInstance = $this->getFieldInstance($fieldClass, $model);
            $model->setFieldInstance($fieldInstance);
        }

        $this->performAjaxValidation($model);

        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Field type has been updated'));
            return $this->refresh();
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => ProfileFieldCategory::find()->all(),
            'fieldClasses' => $this->getFieldClasses(),
            'fieldInstance' => $fieldInstance,
            'fieldClass' => $fieldClass,
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
        /* @var $fieldType ProfileField */
        $fieldType = $this->findModel(['id' => $id]);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$fieldType->delete()) {
            throw new \Exception('Could not delete profile field type entry');
        }

        $this->session->setFlash('success', Yii::t('app', 'Field type has been removed'));
        return $this->redirect($this->request->referrer);
    }

    /**
     * @return BaseType[]
     */
    public function getFieldClasses()
    {
        $baseType = new BaseType();
        return $baseType->getFieldTypes();
    }

    /**
     * @param $fieldClass
     * @param $profileField
     * @return BaseType|null
     */
    protected function getFieldInstance($fieldClass, $profileField)
    {
        $baseType = new BaseType();
        $instance = $baseType->getFieldType($fieldClass, $profileField);
        if ($instance == null) {
            return null;
        }

        return $instance;
    }

    /**
     * @param $model ProfileField
     * @return array|mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFieldClass($model)
    {
        $fieldClass = ArrayHelper::getValue($this->request->post($model->formName()), 'field_class');
        if ($fieldClass == null) {
            $fieldClass = $this->request->get('fieldClass');
        }

        if ($fieldClass !== null) {
            return $fieldClass;
        }

        return $model->field_class;
    }
}
