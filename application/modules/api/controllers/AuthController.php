<?php

namespace app\modules\api\controllers;

use app\modules\api\components\Controller;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\controllers
 */
class AuthController extends Controller
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'token' => [
                'class' => \conquer\oauth2\TokenAction::class,
                'grantTypes' => [
                    'authorization_code' => \conquer\oauth2\granttypes\Authorization::class,
                    'refresh_token' => \conquer\oauth2\granttypes\RefreshToken::class,
                    'client_credentials' => \conquer\oauth2\granttypes\ClientCredentials::class,
                    'password' => \conquer\oauth2\granttypes\UserCredentials::class,
                ]
            ],
        ];
    }
}
