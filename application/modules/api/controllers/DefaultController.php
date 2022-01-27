<?php

namespace app\modules\api\controllers;

use app\modules\api\actions\ErrorAction;
use app\modules\api\components\Controller;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => ErrorAction::class,
        ];
    }
}
