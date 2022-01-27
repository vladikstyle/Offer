<?php

namespace app\modules\admin\controllers;

use app\models\UserFinder;
use app\modules\admin\components\Permission;
use app\modules\admin\forms\BalanceUpdateForm;
use app\payments\AdminBonusTransaction;
use app\helpers\Url;
use app\models\Admin;
use app\models\Profile;
use app\models\User;
use app\modules\admin\models\search\UserSearch;
use conquer\select2\Select2Action;
use Yii;
use yii\base\ExitException;
use yii\base\Module;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class UserController extends \app\modules\admin\components\Controller
{
    /**
     * @var string
     */
    public $model = User::class;

    /**
     * Event is triggered before updating existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    /**
     * Event is triggered after updating existing user.
     * Triggered with app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    /**
     * Event is triggered before updating existing user's profile.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_PROFILE_UPDATE = 'beforeProfileUpdate';
    /**
     * Event is triggered after updating existing user's profile.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_PROFILE_UPDATE = 'afterProfileUpdate';
    /**
     * Event is triggered before confirming existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_CONFIRM = 'beforeConfirm';
    /**
     * Event is triggered after confirming existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_CONFIRM = 'afterConfirm';
    /**
     * Event is triggered before deleting existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    /**
     * Event is triggered after deleting existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';
    /**
     * Event is triggered before blocking existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_BLOCK = 'beforeBlock';
    /**
     * Event is triggered after blocking existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_BLOCK = 'afterBlock';
    /**
     * Event is triggered before unblocking existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_BEFORE_UNBLOCK = 'beforeUnblock';
    /**
     * Event is triggered after unblocking existing user.
     * Triggered with \app\components\user\events\UserEvent.
     */
    const EVENT_AFTER_UNBLOCK = 'afterUnblock';

    /**
     * @var UserFinder
     */
    protected $finder;

    /**
     * @param string $id
     * @param Module $module
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
    public function actions()
    {
        return [
            'ajax' => [
                'class' => Select2Action::class,
                'dataCallback' => [$this, 'ajaxSearch'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permissionBlock' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::MESSAGES,
                'only' => ['block', 'info'],
            ],
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::USERS,
                'except' => ['block', 'info'],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'confirm' => ['post'],
                    'resend-password' => ['post'],
                    'block' => ['post'],
                    'toggle-admin' => ['post'],
                    'toggle-verification' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        $searchModel = new UserSearch();
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
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        $user->scenario = 'update';
        $event = $this->getUserEvent($user);

        $this->performAjaxValidation($user);

        $this->trigger(self::EVENT_BEFORE_UPDATE, $event);
        if ($user->isAdmin && $this->getCurrentUser()->isModerator) {
            $this->session->setFlash('danger', 'You are not allowed to edit admin accounts');
        } elseif ($user->load($this->request->post()) && $user->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Account details have been updated'));
            $this->trigger(self::EVENT_AFTER_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('_account', [
            'user' => $user,
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ExitException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateProfile($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        $profile = $user->profile;

        if ($profile == null) {
            $profile = Yii::createObject(Profile::class);
            $profile->link('user', $user);
        }
        $event = $this->getProfileEvent($profile);
        $this->performAjaxValidation($profile);
        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);

        if ($user->isAdmin && $this->getCurrentUser()->isModerator) {
            $this->session->setFlash('danger', 'You are not allowed to edit admin accounts');
        } elseif ($profile->load($this->request->post()) && $profile->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Profile details have been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('_profile', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ExitException
     */
    public function actionUpdateBalance($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        $profile = $user->profile;

        $balanceForm = new BalanceUpdateForm();
        $this->performAjaxValidation($balanceForm);

        if ($balanceForm->load($this->request->post()) && $balanceForm->validate()) {
            $this->balanceManager->increase(['user_id' => $user->id], $balanceForm->amount, [
                'class' => AdminBonusTransaction::class,
                'notes' => $balanceForm->notes,
            ]);
            $this->session->setFlash('success', Yii::t('app', 'Balance updated'));
            return $this->refresh();
        }

        return $this->render('_balance', [
            'user' => $user,
            'profile' => $profile,
            'currentBalance' => $this->balanceManager->getUserBalance($user->id),
            'balanceForm' => $balanceForm,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInfo($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);

        return $this->render('_info', [
            'user' => $user,
            'isIpBanned' => $this->userManager->checkBan($user->registration_ip, $disableCache = true),
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionConfirm($id)
    {
        $model = $this->findModel($id);
        $event = $this->getUserEvent($model);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);
        $model->confirm();
        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        $this->session->setFlash('success', Yii::t('app', 'User has been confirmed'));

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDelete($id)
    {
        $this->ensureAdmin();

        if ($id == $this->getCurrentUser()->id) {
            $this->session->setFlash('danger', Yii::t('app', 'You can not remove your own account'));
        } else {
            $model = $this->findModel($id);
            $event = $this->getUserEvent($model);
            $this->trigger(self::EVENT_BEFORE_DELETE, $event);
            $model->delete();
            $this->trigger(self::EVENT_AFTER_DELETE, $event);
            $this->session->setFlash('success', Yii::t('app', 'User has been deleted'));
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function actionBlock($id)
    {
        if ($id == $this->getCurrentUser()->id) {
            $this->session->setFlash('danger', Yii::t('app', 'You can not block your own account'));
        } else {
            $user = $this->findModel($id);
            $event = $this->getUserEvent($user);
            if ($user->isAdmin) {
                $this->session->setFlash('danger', Yii::t('app', 'You can not block admin accounts'));
            } elseif ($user->getIsBlocked()) {
                $this->trigger(self::EVENT_BEFORE_UNBLOCK, $event);
                $user->unblock();
                $this->trigger(self::EVENT_AFTER_UNBLOCK, $event);
                $this->session->setFlash('success', Yii::t('app', 'User has been unblocked'));
            } else {
                $this->trigger(self::EVENT_BEFORE_BLOCK, $event);
                $user->block();
                $user->deletePhoto();
                Yii::$app->notificationManager->deleteNotificationsFrom($user);
                $this->trigger(self::EVENT_AFTER_BLOCK, $event);
                $this->session->setFlash('success', Yii::t('app', 'User has been blocked'));
            }
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionToggleAdmin($id)
    {
        $this->ensureAdmin();

        if ($id == $this->getCurrentUser()->id) {
            $this->session->setFlash('danger', Yii::t('app', 'You can not toggle your own admin status.'));
        } else {
            $user = $this->findModel($id);
            if ($user->isAdmin) {
                Admin::remove($user);
                $this->session->setFlash('success', Yii::t('app', 'User has been removed from administrators.'));
            } else {
                Admin::add($user);
                $this->session->setFlash('success', Yii::t('app', 'User has been added to administrators.'));
            }
        }

        return $this->redirect(Url::previous('actions-redirect'));
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
        $user = $this->findModel($id);
        if ($user->profile->is_verified) {
            $user->profile->is_verified = false;
            $user->profile->save(false);
            $this->session->setFlash('success', Yii::t('app', 'Verification badge has been removed from this user.'));
        } else {
            $user->profile->is_verified = true;
            $user->profile->save(false);
            $this->session->setFlash('success', Yii::t('app', 'Verification badge has been added to this user.'));
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionResendPassword($id)
    {
        $user = $this->findModel($id);
        if ($user->isAdmin) {
            throw new ForbiddenHttpException(Yii::t('app', 'Password generation is not possible for admin users'));
        }

        if ($user->resendPassword()) {
            $this->session->setFlash('success', Yii::t('app', 'New Password has been generated and sent to user'));
        } else {
            $this->session->setFlash('danger', Yii::t('app', 'Error while trying to generate new password'));
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param $searchQuery
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function ajaxSearch($searchQuery)
    {
        $users = User::find()
            ->joinWith(['profile'])
            ->andWhere(['or',
                ['like', 'user.username', $searchQuery],
                ['like', 'profile.name', $searchQuery],
            ])
            ->limit(25)
            ->all();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => $user->username,
                'name' => $user->profile->getDisplayName(),
                'username' => $user->username,
                'avatar' => $user->profile->getAvatarUrl(48, 48),
            ];
        }

        return ['results' => $results];
    }

    /**
     * @throws ForbiddenHttpException
     */
    protected function ensureAdmin()
    {
        if (!$this->getCurrentUser()->isAdmin) {
            throw new ForbiddenHttpException(Yii::t('app', 'You are not allowed to perform this action'));
        }
    }
}
