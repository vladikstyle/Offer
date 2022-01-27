<?php

namespace app\controllers;

use app\actions\GlideAction;
use app\actions\UploadAction;
use app\forms\RegistrationForm;
use app\models\Sex;
use app\models\Upload;
use app\traits\AjaxValidationTrait;
use trntv\filekit\events\UploadEvent;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class DefaultController extends \app\base\Controller
{
    use AjaxValidationTrait;

    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        $sizeMultiplier = 1048576; // 1Mb

        return [
            'thumbnail' => [
                'class' => GlideAction::class,
                'imageFile' => function() {
                    $imagePath = $this->request->get('path');
                    if (!Yii::$app->photoStorage->isFileExists($imagePath)) {
                        throw new NotFoundHttpException();
                    }
                    return $imagePath;
                },
            ],
            'upload-photo' => [
                'class' => UploadAction::class,
                'fileStorage' => 'photoStorage',
                'deleteRoute' => '/post/upload-photo-delete',
                'multiple' => true,
                'disableCsrf' => true,
                'maxWidth' => $this->settings->get('common', 'photoMaxWidth', 1500),
                'maxHeight' => $this->settings->get('common', 'photoMaxHeight', 1500),
                'quality' => $this->settings->get('common', 'photoQuality', 90),
                'validationRules' => [
                    [
                        'file', 'image',
                        'minWidth' => $this->settings->get('common', 'photoMinWidth', 500),
                        'minHeight' => $this->settings->get('common', 'photoMinHeight', 500),
                        'maxSize' => $this->settings->get('common', 'photoMaxFileSize', $sizeMultiplier * 10) * $sizeMultiplier,
                        'extensions' => ['jpg', 'jpeg', 'tiff', 'png'],
                    ],
                ],
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
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['upload-photo', 'upload-photo-delete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'thumbnail'],
                        'roles' => ['@', '?'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return int|mixed|string|\yii\console\Response
     * @throws InvalidConfigException
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            return Yii::$app->runAction('dashboard/index');
        }

        /** @var RegistrationForm $registrationForm */
        $registrationForm = Yii::createObject(RegistrationForm::class);

        $this->performAjaxValidation($registrationForm);

        return $this->render('index', [
            'sexModels' => Sex::find()->all(),
            'registrationForm' => $registrationForm,
        ]);
    }
}
