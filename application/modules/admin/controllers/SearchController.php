<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\modules\admin\components\Controller;
use app\modules\admin\components\Permission;
use app\modules\admin\components\Search;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class SearchController extends Controller
{
    /**
     * @var Search
     */
    protected $search;

    public function init()
    {
        parent::init();
        $this->search = Yii::createObject([
            'class' => Search::class,
            'user' => $this->getCurrentUser(),
        ]);
    }

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
            ],
        ]);
    }

    /**
     * @param $q
     * @return string
     * @throws \Exception
     */
    public function actionIndex($q)
    {
        return $this->render('index', [
            'query' => $q,
            'searchProviders' => $this->search->getSearchProviders($q),
        ]);
    }

    /**
     * @param $q
     * @return bool
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetResults($q)
    {
        return $this->sendJson(['results' => $this->search->getResults($q)]);
    }
}
