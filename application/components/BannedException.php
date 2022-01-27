<?php

namespace app\components;

use yii\web\ForbiddenHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components
 */
class BannedException extends ForbiddenHttpException
{
}
