<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\modules\admin\components\Permission;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\admin\models\Photo;
use dosamigos\grid\actions\ToggleAction;
use app\actions\GlideAction;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class PhotoController extends \app\modules\admin\components\Controller
{
    /**
     * @var string|Photo
     */
    public $model = Photo::class;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'toggle' => [
                'class' => ToggleAction::class,
                'modelClass' => $this->model,
                'scenario' => Photo::SCENARIO_TOGGLE,
            ],
            'thumbnail' => [
                'class' => GlideAction::class,
                'imageFile' => function() {
                    $photo = $this->findModel(['id' => $this->request->get('id')]);
                    return $photo->source;
                },
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'cache' => [
                'class' => 'yii\filters\HttpCache',
                'only' => ['thumbnail'],
                'lastModified' => function ($action, $params) {
                    $photo = Photo::findOne(['id' => $this->request->get('id')]);
                    return $photo !== null ? $photo->updated_at : null;
                },
            ],
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::PHOTOS,
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'approve' => ['POST'],
                    'toggle-private' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $query = Photo::find()->joinWith(['userProfile', 'user']);
        $type = $this->request->get('unverified', 1);

        if (!$type == Photo::NOT_VERIFIED) {
            $query = $query->unverified();
        }

        $userId = $this->request->get('userId');
        $user = null;
        if ($userId !== null) {
            $user = $this->userManager->getUserById($userId, ['includeBanned' => true, 'allPhotos' => true]);
            if ($user == null) {
                throw new NotFoundHttpException('User not found');
            }
            $query->forUser($userId);
        }

        return $this->render('index', [
            'type' => $type,
            'user' => $user,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
            ]),
        ]);
    }

    /**
     * @param integer $id
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionApprove($id)
    {
        /* @var $photo Photo */
        $photo = $this->findModel(['id' => $id]);
        if (!$photo->approve(true)) {
            throw new Exception('Could not approve photo entry');
        }

        if ($photo->user->profile->photo_id == null) {
            $this->photoManager->resetUserPhoto($photo->user_id, $photo->id);
        }

        return $this->sendJson([
            'success' => true,
            'message' => Yii::t('app', 'Photo has been approved'),
        ]);
    }

    /**
     * @param integer $id
     * @param bool $locked
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionTogglePrivate($id, $locked = true)
    {
        /* @var $photo Photo */
        $photo = $this->findModel(['id' => $id]);

        if ($photo->isPrivate()) {
            $photo->makePublic();
        } else {
            $photo->setPrivate($locked);
        }
        $photo->approve(true);

        return $this->sendJson([
            'success' => true,
            'message' => Yii::t('app', 'Photo private status has been changed'),
        ]);
    }

    /**
     * @param integer $id
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        /* @var $photo Photo */
        $photo = $this->findModel(['id' => $id]);
        if (!$this->photoManager->deletePhoto($photo)) {
            throw new Exception('Could not delete photo entry');
        }

        return $this->sendJson([
            'success' => true,
            'message' => Yii::t('app', 'Photo has been removed'),
        ]);
    }
}
