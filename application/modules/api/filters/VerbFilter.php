<?php

namespace app\modules\api\filters;

use Yii;
use yii\base\ActionEvent;
use yii\web\MethodNotAllowedHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\filters
 */
class VerbFilter extends \yii\filters\VerbFilter
{
    /**
     * @param ActionEvent $event
     * @return boolean
     * @throws MethodNotAllowedHttpException when the request method is not allowed.
     */
    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed));
        }

        return $event->isValid;
    }
}
