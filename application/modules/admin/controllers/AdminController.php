<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\modules\admin\components\Controller;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class AdminController extends Controller
{
    public $modelClass = Admin::class;

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        /** @var Admin $admin */
        $admin = Yii::createObject(['class' => Admin::class]);

        if ($admin->load($this->request->post()) && $admin->save()) {
            switch ($admin->role) {
                case Admin::ROLE_ADMIN:
                    $this->session->setFlash('success', Yii::t('app', 'User has been added to administrators'));
                        break;
                case Admin::ROLE_MODERATOR:
                    $this->session->setFlash('success', Yii::t('app', 'User has been added to moderators'));
                    break;
            }
            return $this->redirect(['settings/admin']);
        }

        return $this->render('create', ['admin' => $admin]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $admin = $this->findModel(['model' => Admin::class, 'id' => $id]);

        if ($admin->load($this->request->post()) && $admin->save()) {
            $this->session->setFlash('success', Yii::t('app', 'User permissions have been updated'));
            return $this->redirect(['settings/admin']);
        }

        return $this->render('update', ['admin' => $admin]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $admin = $this->findModel(['model' => Admin::class, 'id' => $id]);
        $admin->delete();

        $this->session->setFlash('success', Yii::t('app', 'User has been removed from admins/moderators'));

        return $this->redirect(['settings/admin']);
    }
}
