<?php

namespace app\controllers;

use app\actions\ErrorAction;
use app\actions\ViewAction;
use app\components\AppState;
use app\components\ConsoleRunner;
use app\helpers\Url;
use app\models\Language;
use app\traits\CountryTrait;
use Yii;
use yii\captcha\CaptchaAction;
use yii\web\Cookie;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class SiteController extends \app\base\Controller
{
    use CountryTrait;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'foreColor' => 0x3d74c8,
            ],
            'page' => [
                'class' => ViewAction::class,
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\ExitException
     */
    public function actionApplyUpdates()
    {
        $autoUpdate = Yii::$app->params['autoApplyUpdates'];
        $appState = new AppState();
        $appState->readState();

        if (!$appState->isMaintenance()) {
            if ($this->request->isAjax) {
                return $this->sendJson(['success' => true, 'updated' => true]);
            }
            return $this->redirect('/');
        }

        $isAdmin = !Yii::$app->user->isGuest && $this->getCurrentUser()->isAdmin;
        if ($autoUpdate || ($isAdmin && $this->request->get('runUpdate', 0) == 1)) {
            $consoleRunner = new ConsoleRunner();
            $consoleRunner->run('update/apply');
            if ($isAdmin) {
                $this->session->setFlash('updateSuccess', Yii::t('app', 'YouDate has been updated'));
                return $this->redirect('/' . env('ADMIN_PREFIX'));
            }
        }

        $this->layout = 'maintenance';
        return $this->render($isAdmin ? 'update' : 'maintenance');
    }

    /**
     * @param null $country
     * @param $query
     * @throws \Geocoder\Exception\Exception
     * @throws \yii\base\ExitException
     */
    public function actionFindCities($country = null, $query = null)
    {
        if ($this->isOneCountryOnly() == true ) {
            $country = $this->getDefaultCountry();
            if ($country == null) {
                Yii::warning('Default country is not set');
            }
        }

        if ($country == null) {
            $country = Yii::$app->user->isGuest ?
                Yii::$app->geographer->detectCountry($this->request->userIP) :
                $this->getCurrentUserProfile()->country;
        }

        $this->sendJson(['cities' =>  Yii::$app->geographer->findCities($country, $query)]);
    }

    /**
     * @throws \Geocoder\Exception\Exception
     * @throws \yii\base\ExitException
     */
    public function actionDetectLocation()
    {
        $ipAddress = $this->request->userIP;
        $country = Yii::$app->geographer->detectCountry($ipAddress);
        $city = Yii::$app->geographer->detectCityByIp($ipAddress);

        return $this->sendJson([
            'success' => true,
            'country' => $country,
            'city' => $city !== null ? [
                'geonameId' => $city->geoname_id,
                'name' => $city->getName(),
            ] : null,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionChangeLanguage()
    {
        $preferredLanguage = $this->request->get('language');

        /** @var Language $language */
        $language = Language::find()
            ->where(['language_id' => $preferredLanguage])
            ->andWhere(['in', 'status', [Language::STATUS_ACTIVE, Language::STATUS_BETA]])
            ->one();

        if ($language === null) {
            return $this->redirect(['/']);
        }

        $this->response->cookies->add(new Cookie([
            'name' => 'language',
            'value' => $language->language_id,
            'expire' => time() + 86400 * 365,
        ]));

        if ($this->getCurrentUser()) {
            $profile = $this->getCurrentUserProfile();
            $profile->language_id = $language->language_id;
            $profile->save();
        }

        return $this->redirect($this->request->referrer ?: Yii::$app->homeUrl);
    }
}
