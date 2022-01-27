<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\models\Ban;
use app\modules\admin\components\Permission;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class BanController extends \app\modules\admin\components\Controller
{
    /**
     * @var string
     */
    public $model = Ban::class;

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::BANS,
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
                'query' => Ban::find(),
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
            ]),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\ExitException
     */
    public function actionCreate()
    {
        $ban = new Ban();

        $this->performAjaxValidation($ban);

        if ($ban->load($this->request->post()) && $ban->validate() && $this->userManager->createBan($ban)) {
            $this->session->setFlash('success', Yii::t('app', 'Ban record has been created'));
            return $this->redirect('index');
        }

        return $this->render('create', ['ban' => $ban]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionUpdate($id)
    {
        $ban = $this->findModel(['id' => $id]);

        $this->performAjaxValidation($ban);

        if ($ban->load($this->request->post()) && $ban->validate() && $this->userManager->updateBan($ban)) {
            $this->session->setFlash('success', Yii::t('app', 'Ban record has been updated'));
            return $this->redirect('index');
        }

        return $this->render('create', ['ban' => $ban]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $ban = $this->findModel(['id' => $id]);
        if ($this->userManager->removeBan($ban)) {
            $this->session->setFlash('success', Yii::t('app', 'Ban record has been deleted'));
        }

        return $this->redirect($this->request->referrer ?? 'index');
    }
}
