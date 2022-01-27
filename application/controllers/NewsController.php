<?php

namespace app\controllers;

use app\base\Controller;
use app\models\News;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class NewsController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $newsDataProvider = new ActiveDataProvider([
            'query' => $this->getNewsQuery(),
            'pagination' => ['pageSize' => 10]
        ]);

        return $this->render('index', [
            'newsDataProvider' => $newsDataProvider,
        ]);
    }

    /**
     * @param $alias
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($alias)
    {
        $newsModel = $this->getNewsQuery()->where(['alias' => $alias])->one();
        if ($newsModel === null) {
            throw new NotFoundHttpException();
        }

        $this->view->params['news.id'] = $newsModel->id;

        $latestNews = $this->getNewsQuery()->andWhere(['<>', 'news.id', $newsModel->id])->limit(5)->all();

        return $this->render('view', [
            'newsModel' => $newsModel,
            'latestNews' => $latestNews,
        ]);
    }

    /**
     * @return \app\models\query\NewsQuery
     */
    protected function getNewsQuery()
    {
        return News::find()->latest()->published()
            ->withVoteAggregate('newsLike')
            ->withUserVote('newsLike');
    }
}
