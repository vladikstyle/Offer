<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class BlockController extends \app\base\Controller
{
    /**
     * @var int
     */
    protected $blockedUserId;

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
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
                    'toggle' => ['post'],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        $this->blockedUserId = $this->request->post('blockedUserId');
    }

    /**
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionCreate()
    {
        $blockedUser = Yii::$app->userManager->getUserById($this->blockedUserId);
        if ($blockedUser == null) {
            throw new NotFoundHttpException();
        }

        if ($blockedUser->isAdmin) {
            return $this->sendJson([
                'success' => false,
                'message' => Yii::t('app', 'You can not block administrators'),
            ]);
        }

        if ($this->userManager->blockUser(Yii::$app->user->id, $this->blockedUserId)) {
            return $this->sendJson([
                'success' => true,
                'message' => Yii::t('app', 'User has been blocked'),
            ]);
        }

        return $this->sendJson([
            'success' => false,
            'message' => Yii::t('app', 'Could not block this user'),
        ]);
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\ExitException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        if ($this->userManager->unBlockUser(Yii::$app->user->id, $this->blockedUserId)) {
            return $this->sendJson([
                'success' => true,
                'message' => Yii::t('app', 'User has been unblocked'),
            ]);
        }

        return $this->sendJson([
            'success' => false,
            'message' => Yii::t('app', 'Could find block record for this user'),
        ]);
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidRouteException
     */
    public function actionToggle()
    {
        if ($this->userManager->isUserBlocked(Yii::$app->user->id, $this->blockedUserId)) {
            return $this->runAction('delete');
        } else {
            return $this->runAction('create');
        }
    }
}
