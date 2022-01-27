<?php

namespace youdate\controllers;

use app\base\Controller;
use app\helpers\DarkMode;
use app\traits\DarkModeTrait;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\controllers
 */
class AppearanceController extends Controller
{
    use DarkModeTrait;

    /**
     * @var string
     */
    public $layout = '@app/views/settings/_layout';

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
                    'change' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (!$this->allowOverrideDarkMode()) {
            throw new NotFoundHttpException();
        }

        return $this->render('index', [
            'modes' => DarkMode::getModesList(),
            'darkMode' => $this->getDarkMode(),
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionChange()
    {
        $this->setDarkMode($this->request->post('mode'));

        return $this->redirect(['index']);
    }
}
