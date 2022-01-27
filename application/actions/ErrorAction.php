<?php

namespace app\actions;

use app\components\AppException;
use app\components\BannedException;
use yii\web\HttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\actions
 */
class ErrorAction extends \yii\web\ErrorAction
{
    public function init()
    {
        parent::init();
        if ($this->exception instanceof HttpException) {
            switch ($this->exception->statusCode) {
                case 403:
                case 404:
                case 500:
                    $this->view = 'error/' . $this->exception->statusCode;
                    break;
            }
        }
        if ($this->exception instanceof BannedException) {
            $this->view = 'error/banned';
        }
        if ($this->exception instanceof AppException) {
            $this->view = 'error/app';
        }
    }
}
