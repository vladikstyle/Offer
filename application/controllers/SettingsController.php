<?php

namespace app\controllers;

use app\forms\DataRequestForm;
use app\forms\ProfileExtraForm;
use app\forms\VerificationForm;
use app\models\DataRequest;
use app\models\PhotoAccess;
use app\models\ProfileExtra;
use app\models\ProfileField;
use app\models\ProfileFieldCategory;
use app\models\Verification;
use app\settings\Settings;
use app\models\UserFinder;
use app\settings\SettingsModel;
use app\settings\UserSettings;
use app\forms\SettingsForm;
use app\forms\UploadForm;
use app\models\Profile;
use app\models\User;
use app\traits\CountryTrait;
use Geocoder\Model\Coordinates;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class SettingsController extends \app\base\Controller
{
    use CountryTrait;

    /**
     * Event is triggered before updating user's profile.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_PROFILE_UPDATE = 'beforeProfileUpdate';
    /**
     * Event is triggered after updating user's profile.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_PROFILE_UPDATE = 'afterProfileUpdate';
    /**
     * Event is triggered before updating user's account settings.
     * Triggered with \app\components\user\events\FormEvent.
     */
    const EVENT_BEFORE_ACCOUNT_UPDATE = 'beforeAccountUpdate';
    /**
     * Event is triggered after updating user's account settings.
     * Triggered with \app\components\user\events\FormEvent.
     */
    const EVENT_AFTER_ACCOUNT_UPDATE = 'afterAccountUpdate';
    /**
     * Event is triggered before changing users' email address.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_CONFIRM = 'beforeConfirm';
    /**
     * Event is triggered after changing users' email address.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_CONFIRM = 'afterConfirm';
    /**
     * Event is triggered before disconnecting social account from user.
     * Triggered with \app\components\user\events\ConnectEvent.
     */
    const EVENT_BEFORE_DISCONNECT = 'beforeDisconnect';
    /**
     * Event is triggered after disconnecting social account from user.
     * Triggered with \app\components\user\events\ConnectEvent.
     */
    const EVENT_AFTER_DISCONNECT = 'afterDisconnect';
    /**
     * Event is triggered before deleting user's account.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    /**
     * Event is triggered after deleting user's account.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';
    /**
     * @var UserFinder
     */
    protected $finder;

    /**
     * @var string
     */
    public $layout = '@app/views/settings/_layout';

    /**
     * @param string $id
     * @param \yii\base\Module $module
     * @param UserFinder $finder
     * @param array $config
     */
    public function __construct($id, $module, UserFinder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'disconnect' => ['post'],
                    'delete' => ['post'],
                    'request-data' => ['post'],
                    'photo-access-action' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['confirm'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionProfile()
    {
        /** @var Profile $model */
        $model = $this->finder->findProfileById($this->getCurrentUser()->id);

        if ($model == null) {
            $model = Yii::createObject(Profile::class);
            $model->link('user', $this->getCurrentUser());
        }

        $event = $this->getProfileEvent($model);

        $this->performAjaxValidation($model);

        $formData = ($this->request->post('Profile'));
        if (isset($formData['looking_for_sex_array']) && is_array($formData['looking_for_sex_array'])) {
            $finalValue = 0;
            foreach ($formData['looking_for_sex_array'] as $value) {
                if ($model->isValidSexOption($value)) {
                    $finalValue += $value;
                }
            }
            $model->looking_for_sex = $finalValue;
        }

        $coordinates = Yii::$app->geographer->getCityCoordinates($model->city);
        if ($coordinates instanceof Coordinates) {
            $model->latitude = $coordinates->getLatitude();
            $model->longitude = $coordinates->getLongitude();
        }

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Your profile has been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }

        $profileFields = ProfileField::getFields();
        $profileExtra = ProfileExtra::getExtraFields(Yii::$app->user->id);
        $profileFieldCategories = ProfileFieldCategory::find()
            ->joinWith('profileFields', 'profile')
            ->visible()->sorted()->all();

        $extraModels = [];
        foreach ($profileFieldCategories as $category) {
            $extraModels[$category->alias] = ProfileExtraForm::createFromFields(
                ArrayHelper::getValue($profileFields, $category->alias, []),
                ArrayHelper::getValue($profileExtra, $category->alias, []),
                $category->alias
            );
        }

        return $this->render('profile', [
            'model' => $model,
            'countries' => Yii::$app->geographer->getCountriesList(),
            'profileFields' => $profileFields,
            'profileFieldCategories' => $profileFieldCategories,
            'profileExtra' => $profileExtra,
            'extraModels' => $extraModels,
            'isOneCountryOnly' => $this->isOneCountryOnly(),
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionExtraFields()
    {
        $categoryAlias = $this->request->post('categoryAlias');
        $profileFields = ProfileField::getFields($categoryAlias);
        $profileExtra = ProfileExtra::getExtraFields(Yii::$app->user->id);
        $model = ProfileExtraForm::createFromFields(
            ArrayHelper::getValue($profileFields, $categoryAlias, []),
            ArrayHelper::getValue($profileExtra, $categoryAlias, []),
            $categoryAlias
        );

        $this->performAjaxValidation($model);

        if ($model->load($this->request->post()) && $model->validate()) {
            foreach ($model->getAttributes() as $attribute => $value) {
                ProfileExtra::saveValue(Yii::$app->user->id, $categoryAlias, $attribute, $value);
            }
        }

        $this->session->setFlash('success_' . $categoryAlias,
            Yii::t('app', 'Your profile has been updated')
        );

        return $this->redirect(['profile', '#' => $categoryAlias]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = Yii::createObject(SettingsForm::class);
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_ACCOUNT_UPDATE, $event);
        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Your account details have been updated'));
            $this->trigger(self::EVENT_AFTER_ACCOUNT_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function actionNetworks()
    {
        return $this->render('networks', [
            'user' => $this->getCurrentUser(),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionNotifications()
    {
        $userItems = Yii::$app->notificationManager->getUserSettings();
        $userSettings = UserSettings::forUser(Yii::$app->user->id);
        $settingsModel = SettingsModel::createModel($userItems);
        $settingsModel->setAttributes($userSettings->getUserSetting($settingsModel->getAttributes()));

        if ($settingsModel->load($this->request->post()) && $settingsModel->validate()) {
            $userSettings->setUserSetting($settingsModel->getAttributes());
            $this->session->setFlash('success', Yii::t('app', 'Settings have been saved'));
            return Yii::$app->controller->refresh();
        }

        return $this->render('notifications', [
            'settingsModel' => $settingsModel,
            'items' => $userItems,
        ]);
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function actionVerification()
    {
        $verificationForm = new VerificationForm();
        $verificationForm->userId = $this->getCurrentUser()->id;
        $verificationEntry = Verification::findOne(['user_id' => $this->getCurrentUser()->id]);

        if ($this->request->isPost) {
            $verificationForm->photo = UploadedFile::getInstanceByName('photo');
            if ($verificationForm->createVerificationEntry()) {
                if ($this->request->isAjax) {
                    return $this->sendJson(['success' => true]);
                }
                return $this->refresh();
            }
        }

        return $this->render('verification', [
            'verificationForm' => $verificationForm,
            'verificationEntry' => $verificationEntry,
            'user' => $this->getCurrentUser(),
            'profile' => $this->getCurrentUserProfile(),
        ]);
    }

    /**
     * @param $id
     * @param $code
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function actionConfirm($id, $code)
    {
        $user = $this->finder->findUserById($id);

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);
        $user->attemptEmailChange($code);
        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        return $this->redirect(['account']);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDisconnect($id)
    {
        $account = $this->finder->findAccount()->byId($id)->one();

        if ($account === null) {
            throw new NotFoundHttpException();
        }
        if ($account->user_id != Yii::$app->user->id) {
            throw new ForbiddenHttpException();
        }

        $event = $this->getConnectEvent($account, $account->user);

        $this->trigger(self::EVENT_BEFORE_DISCONNECT, $event);
        $account->delete();
        $this->trigger(self::EVENT_AFTER_DISCONNECT, $event);

        return $this->redirect(['networks']);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionPhotos()
    {
        $dataProvider = $this->photoManager->getPhotosProvider([
            'userId' => Yii::$app->user->id,
            'verifiedOnly' => false,
            'unverifiedFirst' => true,
        ]);

        return $this->render('photos', [
            'dataProvider' => $dataProvider,
            'photoModerationEnabled' => Yii::$app->settings->get('common', 'photoModerationEnabled'),
        ]);
    }

    /**
     * @return string
     */
    public function actionAccessRequests()
    {
        return $this->render('access-requests', [
            'dataProvider' => $this->photoManager->getPhotoAccessProvider($this->getCurrentUser()),
        ]);
    }

    /**
     * @param $fromUserId
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\base\Exception
     */
    public function actionPhotoAccessAction($fromUserId, $action)
    {
        $fromUser = Yii::$app->userManager->getUserById($fromUserId);
        $toUser = $this->getCurrentUser();

        if ($fromUser == null) {
            throw new NotFoundHttpException();
        }

        if ($action == PhotoAccess::STATUS_APPROVED || $action == PhotoAccess::STATUS_REJECTED) {
            Yii::$app->photoManager->approveOrRejectPhotoAccess($fromUser, $toUser, $action);
            return $this->sendJson([
                'success' => true,
                'message' => Yii::t('app', 'Private photo access has been changed for this user'),
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionUpload()
    {
        /** @var $settings Settings */
        $settings = Yii::$app->settings;

        $uploadForm = new UploadForm();
        $profile = $this->getCurrentUserProfile();
        $autoSetPhoto = !$settings->get('common', 'photoModerationEnabled', true);

        if ($uploadForm->load($this->request->post()) && $uploadForm->validate()) {
            $photoIDs = $uploadForm->createPhotos();
            if ($autoSetPhoto && $profile->photo_id == null && count($photoIDs)) {
                $this->photoManager->resetUserPhoto($profile->user_id, $photoIDs[0]);
            }
            $this->session->set('uploadedPhotos', $photoIDs);
            return $this->redirect('photos');
        }

        return $this->render('upload', [
            'uploadForm' => new UploadForm(),
            'settings' => $settings->get('common'),
            'currentPhotosCount' => $this->photoManager->getPhotosCountForUser($this->getCurrentUser()),
        ]);
    }

    /**
     * @return string
     */
    public function actionBlockedUsers()
    {
        return $this->render('blocked-users', [
            'blockedUsers' => $this->userManager->getBlockedUsers(Yii::$app->user->id)
        ]);
    }

    /**
     * @return string
     */
    public function actionData()
    {
        $dataRequests = DataRequest::find()
            ->where(['user_id' => $this->getCurrentUser()->id])
            ->limit(3)
            ->orderBy('id desc')
            ->all();

        return $this->render('data', [
            'dataRequestForm' => new DataRequestForm(),
            'dataRequests' => $dataRequests,
            'profile' => $this->getCurrentUserProfile(),
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionRequestData()
    {
        $dataRequestForm = new DataRequestForm();
        $dataRequestForm->setUser($this->getCurrentUser());
        if ($dataRequestForm->load($this->request->post()) && $dataRequestForm->validate()) {
            if (Yii::$app->dataExportManager->createRequest($this->getCurrentUser(), $dataRequestForm->format)) {
                $this->session->setFlash('data-request',
                    Yii::t('app', 'Your archive will be created soon. Download link will be sent to your e-mail')
                );
            }
        } else {
            $this->session->setFlash('danger', $dataRequestForm->getFirstError('format'));
        }

        return $this->redirect('data');
    }

    /**
     * @param $code
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDownloadData($code)
    {
        /** @var DataRequest $dataRequest */
        $dataRequest = DataRequest::find()
            ->where(['user_id' => $this->getCurrentUser()->id, 'code' => $code, 'status' => DataRequest::STATUS_DONE])
            ->one();

        if ($dataRequest == null) {
            throw new NotFoundHttpException();
        }

        $filePath = Yii::$app->dataExportManager->getFilePath($dataRequest);
        $userProfile = $dataRequest->user->profile;

        if ($filePath !== null) {
            $attachmentName = sprintf('%s %s %s.zip',
                Yii::t('app', 'Data'),
                $userProfile->getDisplayName(),
                $dataRequest->getRequestDate()
            );
            $this->response->sendFile($filePath, $attachmentName);
            Yii::$app->end();
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return \yii\web\Response|string
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        if ($this->request->isGet) {
            return $this->render('delete');
        }

        /** @var User $user */
        $user = $this->getCurrentUser();
        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
        Yii::$app->user->logout();
        $user->delete();
        $this->trigger(self::EVENT_AFTER_DELETE, $event);

        $this->session->setFlash('info', Yii::t('app', 'Your account has been completely deleted'));

        return $this->goHome();
    }
}
