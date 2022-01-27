<?php

namespace youdate\widgets;

use app\models\News;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class ImportantNewsWidget extends Widget
{
    /**
     * @return string
     */
    public function run()
    {
        $newsModel = News::find()->important()->published()->latest()->one();
        if ($newsModel === null) {
            return '';
        }

        $newsId = $this->view->params['news.id'] ?? null;
        $cookieKey = 'news' . $newsModel->id;
        $showNewsModel = isset($_COOKIE[$cookieKey]) ? (bool) $_COOKIE[$cookieKey] : true;

        if ($newsModel !== null && $newsId !== $newsModel->id && $showNewsModel) {
            return $this->render('news/important', ['newsModel' => $newsModel]);
        }
    }
}
