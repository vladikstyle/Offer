<?php

namespace app\controllers;

use app\forms\UserSearchForm;
use app\models\ProfileField;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class DirectoryController extends \app\base\Controller
{
    const SEARCH_PARAMS_KEY = 'searchRequestParams';

    /**
     * Main page
     *
     * @return string
     * @throws \Exception
     */
    public function actionIndex()
    {
        if ($this->settings->get('frontend', 'siteHideDirectoryFromGuests', false) == true && Yii::$app->user->isGuest) {
            return $this->redirect(['/security/login']);
        }

        $params = [];

        // save user's search params
        $requestParams = $this->request->get();
        $savedParams = $this->session->get(self::SEARCH_PARAMS_KEY);
        $resetSavedParams = $this->request->get('reset', false);
        if (count($requestParams) == 0 && $savedParams !== null && $resetSavedParams === false) {
            $requestParams = $savedParams;
        } elseif (count($requestParams) && $resetSavedParams === false) {
            $this->session->set(self::SEARCH_PARAMS_KEY, $requestParams);
        }
        if ($resetSavedParams == true) {
            $this->session->remove(self::SEARCH_PARAMS_KEY);
            return $this->redirect(['index']);
        }

        $profileFields = ProfileField::getProfileFieldsForSearch();
        $searchForm = new UserSearchForm();
        $searchForm->setProfile($this->getCurrentUserProfile());
        $searchForm->load($requestParams);
        $params['currentUser'] = $this->getCurrentUser();
        $params['searchForm'] = $searchForm;
        $params['profileFields'] = $profileFields;
        $currentProfile = $this->getCurrentUserProfile();

        $currentCity = null;
        if (!Yii::$app->user->isGuest) {
            $params['hideCurrentUser'] = true;
            $cityId = isset($searchForm->city) || $currentProfile === null ? $searchForm->city : $currentProfile->city;
        } else {
            $cityId = $searchForm->city;
        }

        $cityName = Yii::$app->geographer->getCityName($cityId);
        $currentCity = [
            'value' => $searchForm->city,
            'title' => $cityName,
            'city' => $cityName,
            'region' => null,
            'country' => null,
            'population' => null,
        ];

        return $this->render('index', [
            'dataProvider' => Yii::$app->userManager->getUsersProvider($params),
            'user' => $this->getCurrentUser(),
            'profile' => $this->getCurrentUserProfile(),
            'searchForm' => $searchForm,
            'profileFields' => $profileFields,
            'countries' => Yii::$app->geographer->getCountriesList(),
            'currentCity' => $currentCity,
            'alreadyBoosted' => Yii::$app->balanceManager->isAlreadyBoosted(Yii::$app->user->id),
        ]);
    }
}
