<?php

namespace app\controllers;

use app\actions\GlideAction;
use app\actions\UploadAction;
use app\models\Photo;
use app\settings\Settings;
use app\models\User;
use app\models\Upload;
use Yii;
use yii\base\Event;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use trntv\filekit\events\UploadEvent;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class PhotoController extends \app\base\Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['upload-photo', 'upload-photo-delete', 'set-main', 'delete', 'toggle-private'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['thumbnail'],
                    ],

                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-main' => ['post'],
                    'toggle-private' => ['post'],
                    'upload-photo' => ['post'],
                    'upload-photo-delete' => ['delete'],
                    'delete' => ['post'],
                ],
            ],
            'cache' => [
                'class' => \yii\filters\HttpCache::class,
                'only' => ['thumbnail'],
                'lastModified' => function ($action, $params) {
                    $photo = $this->photoManager->getPhoto($this->request->get('id'), [
                        'verifiedOnly' => false,
                    ]);
                    if ($photo !== null && isset($photo->updated_at)) {
                        return $photo->updated_at;
                    }
                    return null;
                },
            ],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        $sizeMultiplier = 1024 * 1024;
        $photosCount = 0;
        if ($this->getCurrentUser()) {
            $photosCount = $this->photoManager->getPhotosCountForUser($this->getCurrentUser());
        }

        return [
            'thumbnail' => [
                'class' => GlideAction::class,
                'imageFile' => function() {
                    $photoId = $this->request->get('id');
                    $photoCached = $this->cache->get("photo{$photoId}_source");
                    if ($photoCached == null) {
                        $photo = $this->photoManager->getPhoto($photoId, [
                            'verifiedOnly' => false,
                        ]);
                        if ($photo == null) {
                            throw new NotFoundHttpException('Photo not found');
                        }
                        $this->cache->set("photo{$photoId}_source", $photo->source);
                        $photoCached = $photo->source;
                    }
                    return $photoCached;
                },
            ],
            'upload-photo' => [
                'class' => UploadAction::class,
                'fileStorage' => 'photoStorage',
                'deleteRoute' => '/photo/upload-photo-delete',
                'multiple' => true,
                'disableCsrf' => true,
                'maxWidth' => $this->settings->get('common', 'photoMaxWidth', 1500),
                'maxHeight' => $this->settings->get('common', 'photoMaxHeight', 1500),
                'quality' => $this->settings->get('common', 'photoQuality', 90),
                'modelAttributes' => ['photosCount' => $photosCount],
                'validationRules' => [
                    [
                        'file', 'image',
                        'minWidth' => $this->settings->get('common', 'photoMinWidth', 500),
                        'minHeight' => $this->settings->get('common', 'photoMinHeight', 500),
                        'maxSize' => $this->settings->get('common', 'photoMaxFileSize', $sizeMultiplier * 10) * $sizeMultiplier,
                        'extensions' => ['jpg', 'jpeg', 'tiff', 'png'],
                    ],
                    [
                        'photosCount',
                        'integer',
                        'min' => 0,
                        'max' => $this->settings->get('common', 'photoMaxPerProfile', 50) - 1,
                        'tooBig' => Yii::t('app', 'Maximum photos per profile is {0}', $this->settings->get('common', 'photoMaxPerProfile', 50)),
                    ],
                ],
                'on afterValidation' => function (Event $event) {
                    /** @var UploadAction $uploadAction */
                    $uploadAction = $event->sender;
                    $uploadAction->modelAttributes['photosCount'] += 1;
                },
                'on afterSave' => function (UploadEvent $event) {
                    $file = $event->file;
                    $upload = new Upload();
                    $upload->path = $file->getPath();
                    if (!$upload->save()) {
                        throw new \Exception('Could not save upload file info');
                    }
                },
            ],
            'upload-photo-delete' => [
                'class' => \trntv\filekit\actions\DeleteAction::class,
                'fileStorage' => 'photoStorage',
                'on afterDelete' => function (UploadEvent $event) {
                    $file = $event->file;
                    $upload = Upload::findOne(['user_id' => Yii::$app->user->id, 'path' => $file->getPath()]);
                    if ($upload && !$upload->delete()) {
                        throw new \Exception('Could not delete upload file info');
                    }
                },
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if ($action->id == 'thumbnail') {
            $this->prepareData = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @param $id
     * @return bool
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\base\ExitException
     */
    public function actionSetMain($id)
    {
        /** @var Settings $settings */
        $settings = Yii::$app->settings;
        $verifiedOnly = $settings->get('common', 'photoModerationEnabled');
        $photo = $this->getUserPhoto($id, ['verifiedOnly' => false]);

        if ($verifiedOnly && !$photo->is_verified) {
            return $this->sendJson([
                'success' => false,
                'message' => Yii::t('app', 'You\'re not allowed to set unverified photo as your main photo'),
            ]);
        }

        if ($photo->is_private) {
            return $this->sendJson([
                'success' => false,
                'message' => Yii::t('app', 'You can not set private photo as your main photo'),
            ]);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $profile = $user->profile;
        $profile->photo_id = $photo->id;

        if ($profile->save(false)) {
            return $this->sendJson([
                'success' => true,
                'message' => Yii::t('app', 'Your primary photo has been set'),
            ]);
        }

        return $this->sendJson([
            'success' => false,
            'message' => Yii::t('app', 'App error'),
            'errors' => $profile->errors,
        ]);
    }

    /**
     * @param $id
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionTogglePrivate($id)
    {
        $photo = $this->getUserPhoto($id, ['verifiedOnly' => false]);

        $profile = $this->getCurrentUserProfile();
        if ($profile->photo_id == $photo->id && $photo->is_private == 0) {
            return $this->sendJson([
                'success' => false,
                'message' => Yii::t('app', 'You can not toggle private status for your main photo'),
            ]);
        }

        if ($photo->is_private == Photo::PRIVATE_LOCKED) {
            return $this->sendJson([
                'success' => false,
                'message' => Yii::t('app', 'Visibility of this photo has been set by the administrator'),
            ]);
        }

        if ($photo->togglePrivate(true)) {
            return $this->sendJson([
                'success' => true,
                'message' => Yii::t('app', 'Photo visibility has been changed'),
            ]);
        }

        return $this->sendJson([
            'success' => false,
            'message' => Yii::t('app', 'App error'),
            'errors' => $photo->errors,
        ]);
    }

    /**
     * @param $id
     * @return bool
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\ExitException
     */
    public function actionDelete($id)
    {
        $photo = $this->getUserPhoto($id, ['verifiedOnly' => false]);
        $this->photoManager->deletePhoto($photo);
        $profile = $this->getCurrentUserProfile();
        if ($profile->photo_id == $id) {
            $this->photoManager->resetUserPhoto($profile->user_id);
        }

        return $this->sendJson([
            'success' => true,
            'message' => Yii::t('app', 'Photo has been deleted'),
        ]);
    }

    /**
     * @param $id
     * @param array $params
     * @return \app\models\Photo|array|null
     * @throws NotFoundHttpException
     */
    protected function getUserPhoto($id, $params = [])
    {
        $photo = $this->photoManager->getUserPhoto(Yii::$app->user->id, $id, $params);
        if ($photo == null) {
            throw new NotFoundHttpException('Photo not found');
        }

        return $photo;
    }
}
