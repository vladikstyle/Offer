<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\models\Group;
use app\modules\admin\components\Permission;
use app\modules\admin\models\search\GroupSearch;
use app\traits\AjaxValidationTrait;
use app\traits\EventTrait;
use app\helpers\Url;
use Yii;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class GroupController extends \app\modules\admin\components\Controller
{
    use EventTrait;
    use AjaxValidationTrait;

    /**
     * @var string
     */
    public $model = Group::class;

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::GROUPS,
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'toggle-verification' => ['post'],
                    'toggle-block' => ['post'],
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
        Url::remember('', 'actions-redirect');
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search($this->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ExitException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        Url::remember('', 'actions-redirect');
        $group = $this->findModel($id);

        $this->performAjaxValidation($group);

        if ($group->load($this->request->post()) && $group->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Group has been updated'));
            return $this->refresh();
        }

        return $this->render('update', [
            'group' => $group,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        $this->session->setFlash('success', Yii::t('app', 'Group has been deleted'));

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionToggleVerification($id)
    {
        /** @var Group $group */
        $group = $this->findModel($id);
        $group->is_verified = !$group->is_verified;
        $group->save();

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionToggleBlock($id)
    {
        /** @var Group $group */
        $group = $this->findModel($id);
        if ($group->visibility !== Group::VISIBILITY_BLOCKED) {
            $group->visibility = Group::VISIBILITY_BLOCKED;
        } else {
            $group->visibility = Group::VISIBILITY_PRIVATE;
        }

        if ($group->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Group info has been updated'));
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }
}
