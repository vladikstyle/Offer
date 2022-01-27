<?php

namespace app\modules\admin\actions;

use app\modules\admin\components\Controller;
use yii\web\HttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\actions
 *
 * @property Controller $controller
 */
class ErrorAction extends \yii\web\ErrorAction
{
    public function init()
    {
        parent::init();
        if ($this->exception instanceof HttpException) {
            switch ($this->exception->statusCode) {
                case 403:
                    $this->view = 'error-403';
                    break;
            }
        }
    }
}
