<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\models\Order;
use app\modules\admin\components\Permission;
use app\modules\admin\models\search\OrderSearch;
use app\traits\AjaxValidationTrait;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class OrderController extends \app\modules\admin\components\Controller
{
    use AjaxValidationTrait;

    /**
     * @var string
     */
    public $model = Order::class;

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::ORDERS,
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
