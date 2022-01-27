<?php

namespace app\controllers;

use app\forms\PaypalPaymentForm;
use app\forms\PremiumSettingsForm;
use app\forms\SimplePaymentForm;
use app\forms\SpotlightForm;
use app\forms\StripePaymentForm;
use app\models\Currency;
use app\models\Price;
use app\payments\CheckoutHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class BalanceController extends \app\base\Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'services';
    /**
     * @var array
     */
    public $disableCsrfForRoutes = [
        'stripe-webhook',
        'robokassa-success',
        'robokassa-failure',
    ];

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
                    [
                        'allow' => true,
                        'roles' => ['@', '?'],
                        'actions' => [
                            'stripe-webhook',
                            'robokassa-success',
                            'robokassa-failure',
                        ],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'stripe-create-session' => ['post'],
                    'stripe-webhook' => ['post'],
                    'process-paypal' => ['post'],
                    'process-robokassa' => ['post'],
                    'activate-premium' => ['post'],
                    'rise-up' => ['post'],
                    'spotlight-submit' => ['post'],
                    'robokassa-success' => ['post'],
                    'robokassa-failure' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $whiteListActions = ['spotlight-submit', 'rise-up'];
        if (!$this->balanceManager->isPremiumFeaturesEnabled() && !in_array($action->id, $whiteListActions)) {
            throw new NotFoundHttpException();
        }

        if (in_array($action->id, $this->disableCsrfForRoutes)) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionServices()
    {
        $user = $this->getCurrentUser();

        return $this->render('services', [
            'currentBalance' => $this->balanceManager->getUserBalance($user->id),
            'boostPrice' => $this->balanceManager->getBoostPrice(),
            'boostDuration' => $this->balanceManager->getBoostDuration(),
            'premiumPrice' => $this->balanceManager->getPremiumPrice(),
            'premiumDuration' => $this->balanceManager->getPremiumDuration(),
            'userBoost' => $user->boost,
            'userPremium' => $user->premium,
            'alreadyBoosted' => $this->balanceManager->isAlreadyBoosted($user->id),
            'premiumSettings' => PremiumSettingsForm::fromUserPremium($user->premium),
        ]);
    }

    /**
     * @return string
     */
    public function actionTransactions()
    {
        $userId = $this->getCurrentUser()->id;

        return $this->render('transactions', [
            'currentBalance' => $this->balanceManager->getUserBalance($userId),
            'dataProvider' => $this->balanceManager->getTransactionsProvider($userId),
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionBuy()
    {
        $user = $this->getCurrentUser();
        $rate = $this->settings->get('common', 'paymentRate');
        $currency = Currency::getCurrency($this->settings->get('common', 'paymentCurrency'));
        $stripePublishableKey = $this->settings->get('common', 'paymentStripePublishableKey');
        $siteName = $this->settings->get('frontend', 'siteName');

        return $this->render('buy', [
            'currentBalance' => $this->balanceManager->getUserBalance($user->id),
            'rate' => $rate,
            'currency' => $currency,
            'stripePublishableKey' => $stripePublishableKey,
            'userId' => $user->id,
            'userEmail' => $user->email,
            'siteName' => $siteName,
            'prices' => Price::find()->orderBy('credits asc')->all(),
            'stripeEnabled' => $this->settings->get('common', 'paymentStripeEnabled'),
            'paypalEnabled' => $this->settings->get('common', 'paymentPaypalEnabled'),
            'robokassaEnabled' => $this->settings->get('common', 'robokassaEnabled'),
        ]);
    }

    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function actionStripeCreateSession()
    {
        $form = new StripePaymentForm();
        if (!$form->load($this->request->post()) || !$form->validate()) {
            $this->session->setFlash('danger',
                Yii::t('app', 'Unknown payment error occurred. Please try again later')
            );
        }

        $checkout = CheckoutHelper::getStripe($this->getCurrentUser(), $form->credits);
        try {
            $session = $checkout->createSession();
            return $this->sendJson([
                'success' => true,
                'sessionId' => $session->id,
            ]);
        } catch (\Exception $e) {
            $this->response->statusCode = 400;
            return $this->sendJson([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $sessionId
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionStripeSuccess($sessionId)
    {
        $checkout = CheckoutHelper::getStripe($this->getCurrentUser());
        $checkout->setUser($this->getCurrentUser());
        $checkout->validatePayment($sessionId);

        return $this->redirect(['buy']);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionStripeCancel()
    {
        $this->session->setFlash('warning', Yii::t('app', 'Payment canceled'));

        return $this->redirect(['buy']);
    }

    /**
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionStripeWebhook()
    {
        $checkout = CheckoutHelper::getStripe($this->getCurrentUser());
        $payload = @file_get_contents('php://input');

        if ($checkout->handleWebhook($payload)) {
            $this->response->statusCode = 200;
            $this->response->send();
        } else {
            throw new BadRequestHttpException();
        }
    }

    /**
     * @return \yii\web\Response|null
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionProcessPaypal()
    {
        $form = new PaypalPaymentForm();
        if (!$form->load($this->request->post()) || !$form->validate()) {
            $this->session->setFlash('danger',
                Yii::t('app', 'Unknown payment error occurred. Please try again later'));
            return $this->redirect(['buy']);
        }

        $checkout = CheckoutHelper::getPaypal($this->getCurrentUser(), $form->credits);
        $checkout->checkout();

        return null;
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionPaypalSuccess()
    {
        $paymentId = $this->request->get('token', null);
        if (!$paymentId) {
            $this->redirect(['buy']);
        }

        $checkout = CheckoutHelper::getPaypal($this->getCurrentUser());
        $checkout->validatePayment($paymentId);

        return $this->redirect(['buy']);
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionPaypalFailure()
    {
        $paymentId = $this->request->get('token', null);
        if (!$paymentId) {
            $this->redirect(['buy']);
        }

        $checkout = CheckoutHelper::getPaypal($this->getCurrentUser());
        $checkout->cancelPayment($paymentId);

        return $this->redirect(['buy']);
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionProcessRobokassa()
    {
        $form = new SimplePaymentForm();
        if (!$form->load($this->request->post()) || !$form->validate()) {
            $this->session->setFlash('danger',
                Yii::t('app', 'Unknown payment error occurred. Please try again later'));
            return $this->redirect(['buy']);
        }

        $checkout = CheckoutHelper::getRobokassa($this->getCurrentUser(), $form->credits);
        $checkout->checkout();

        return null;
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionRobokassaSuccess()
    {
        $checkout = CheckoutHelper::getRobokassa($this->getCurrentUser());
        $checkout->validatePayment($this->request->post());

        return $this->redirect(['buy']);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionRobokassaFailure()
    {
        $this->session->setFlash('warning', Yii::t('app', 'Payment canceled'));

        return $this->redirect(['buy']);
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionRiseUp()
    {
        $user = $this->getCurrentUser();
        if ($this->balanceManager->boostUser($user->id)) {
            $this->session->setFlash('user-boost',
                Yii::t('app', 'Your profile has been raised up in search')
            );
        } else {
            $this->session->setFlash('user-boost',
                Yii::t('app', 'You don\'t have enough credits for this operation')
            );
        }

        return $this->redirect(['services#rise-up']);
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionActivatePremium()
    {
        $user = $this->getCurrentUser();
        if ($this->balanceManager->activatePremium($user->id)) {
            $this->session->setFlash('user-premium',
                Yii::t('app', 'Premium features activated')
            );
        } else {
            $this->session->setFlash('user-premium',
                Yii::t('app', 'You don\'t have enough credits for this operation')
            );
        }

        return $this->redirect(['services#premium']);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionPremiumSettings()
    {
        $user = $this->getCurrentUser();
        if (!$user->isPremium) {
            return $this->redirect(['services']);
        }

        $form = new PremiumSettingsForm();
        if ($form->load($this->request->post()) && $form->validate()) {
            $user->premium->show_online_status = $form->showOnlineStatus;
            $user->premium->incognito_active = $form->incognitoActive;
            $user->premium->save();
            $this->session->setFlash('user-premium', Yii::t('app', 'Premium settings saved'));
        }

        return $this->redirect(['services']);
    }

    /**
     * @throws \yii\base\ExitException
     * @throws \yii\db\Exception
     */
    public function actionSpotlightSubmit()
    {
        $user = $this->getCurrentUser();
        $form = new SpotlightForm();
        $form->load($this->request->post());
        if ($form->photoId == null) {
            $form->photoId = $this->getCurrentUserProfile()->photo_id;
        }

        if ($form->validate()) {
            if ($this->balanceManager->submitSpotlight($user->id, $form->photoId, $form->message)) {
                return $this->sendJson([
                    'success' => true,
                    'message' => Yii::t('app','Photo has been placed on spotlight'),
                    'balance' => $this->balanceManager->getUserBalance(Yii::$app->user->id),
                ]);
            }
        }

        return $this->sendJson([
            'success' => false,
            'errors' => $form->errors,
        ]);
    }
}
