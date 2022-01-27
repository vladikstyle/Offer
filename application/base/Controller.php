<?php

namespace app\base;

use app\models\User;
use app\traits\AjaxValidationTrait;
use app\traits\CacheTrait;
use app\traits\DarkModeTrait;
use app\traits\EventTrait;
use app\traits\managers\BalanceManagerTrait;
use app\traits\managers\GiftManagerTrait;
use app\traits\managers\GroupManagerTrait;
use app\traits\managers\GuestManagerTrait;
use app\traits\managers\LikeManagerTrait;
use app\traits\managers\MessageManagerTrait;
use app\traits\managers\NotificationManagerTrait;
use app\traits\managers\PhotoManagerTrait;
use app\traits\managers\UserManagerTrait;
use app\traits\CurrentUserTrait;
use app\traits\RequestResponseTrait;
use app\traits\SessionTrait;
use app\traits\SettingsTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\base
 * @property View $view
 */
class Controller extends \yii\web\Controller
{
    /** Framework related */
    use CacheTrait, RequestResponseTrait, SessionTrait;

    /** App related */
    use AjaxValidationTrait, CurrentUserTrait, EventTrait, SettingsTrait,
        GroupManagerTrait, UserManagerTrait,
        BalanceManagerTrait, GiftManagerTrait, LikeManagerTrait,
        GuestManagerTrait, PhotoManagerTrait, NotificationManagerTrait, MessageManagerTrait;

    const EVENT_BEFORE_INIT = 'beforeInit';
    const EVENT_AFTER_INIT = 'afterInit';

    /**
     * @var bool
     */
    public $prepareData = true;
    /**
     * @var ActiveRecord
     */
    public $model;

    public function init()
    {
        $this->trigger(self::EVENT_BEFORE_INIT);
        parent::init();
        $this->trigger(self::EVENT_AFTER_INIT);
    }

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest && $this->prepareData) {
            $this->initUserData();
            $this->updateOnline();
        }

        return parent::beforeAction($action);
    }

    /**
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render($view, $params = [])
    {
        if ($this->request->isAjax) {
            return $this->renderAjax($view, $params);
        }

        return parent::render($view, $params);
    }

    /**
     * @param $params
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findModel($params)
    {
        if (!is_array($params)) {
            $params = ['id' => $params];
        }
        $modelClass = isset($params['model']) ? ArrayHelper::remove($params, 'model') : $this->model;
        $model = call_user_func([$modelClass, 'find'])->where($params)->one();
        if ($model == null) {
            throw new NotFoundHttpException('Model not found');
        }

        return $model;
    }

    protected function initUserData()
    {
        /** @var User $currentUser */
        $currentUser = $this->getCurrentUser();
        if ($currentUser->isBlocked) {
            Yii::$app->user->logout();
            $this->response->redirect(['/default/index']);
        }
        $this->view->params['counters.messages.new'] = $this->messageManager->getNewMessagesCount($currentUser->id);
        $this->view->params['user.id'] = $currentUser->id;
        $this->view->params['user.displayName'] = $currentUser->profile->getDisplayName();
        $this->view->params['user.email'] = $currentUser->email;
        $this->view->params['user.hasPhoto'] = $currentUser->profile->photo_id !== null;
        $this->view->params['user.avatar'] = $currentUser->profile->getAvatarUrl();
        $this->view->params['user.confirmed'] = $currentUser->isConfirmed;
        $this->view->params['user.balance'] = $this->balanceManager->getUserBalance($currentUser->id);
        $this->view->params['user.ads.hide'] = $currentUser->isPremium;
        $this->view->params['site.premiumFeatures.enabled'] = $this->balanceManager->isPremiumFeaturesEnabled();
        $this->view->params['site.groups.enabled'] = $this->groupManager->isGroupsFeatureEnabled();
        $this->view->params['site.emoji'] = Yii::$app->emoji->getEmoji();
    }

    /**
     * @return \app\models\User|array|null|ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function getTargetUser()
    {
        if (($userId = $this->request->get('toUserId')) !== null) {
            $user = $this->userManager->getUserById($userId);
            if ($user !== null) {
                return $user;
            }
        }

        throw new NotFoundHttpException('Target user not found');
    }

    protected function updateOnline()
    {
        $user = $this->getCurrentUser();
        $lastOnline = $this->session->get('lastOnline');
        if ($user && ($lastOnline == null || time() - $lastOnline > Yii::$app->params['onlineThreshold']) ){
            $lastOnline = time();
            $this->session->set('lastOnline', $lastOnline);
            $user->updateOnline($lastOnline);
        }
    }

    /**
     * @param $data
     * @param int $statusCode
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function sendJson($data, $statusCode = 200)
    {
        $this->response->format = Response::FORMAT_JSON;
        $this->response->data = $data;
        $this->response->statusCode = $statusCode;
        $this->response->send();

        Yii::$app->end();

        return true;
    }
}
