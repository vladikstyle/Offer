<?php

namespace app\modules\admin\controllers;

use app\helpers\Url;
use app\models\Admin;
use app\models\Message;
use app\models\MessageAttachment;
use app\modules\admin\components\Permission;
use app\modules\admin\models\search\MessageSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class MessageController extends \app\modules\admin\components\Controller
{
    /**
     * @var string
     */
    public $model = \app\models\Message::class;

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::MESSAGES,
            ],
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionIndex()
    {
        $searchModel = new MessageSearch();
        Url::remember(Url::current(), 'actions-redirect');

        return $this->render('index', [
            'dataProvider' => $searchModel->search($this->request->get()),
            'searchModel' => $searchModel,
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
        /** @var Message $model */
        $model = $this->findModel($id);
        $attachments = $model->attachments;
        if ($model->delete()) {
            if (count($attachments)) {
                foreach ($attachments as $attachment) {
                    if ($attachment->type == MessageAttachment::TYPE_IMAGE) {
                        Yii::$app->photoStorage->delete($attachment->data);
                    }
                }
            }
            $this->session->setFlash('success', Yii::t('app', 'Message has been deleted'));
        }

        return $this->redirect(['index']);
    }
}
