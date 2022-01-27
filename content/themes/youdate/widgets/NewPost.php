<?php

namespace youdate\widgets;

use app\forms\PostForm;
use Yii;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class NewPost extends Widget
{
    /**
     * @var string
     */
    public $route;

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        return $this->render('post/new', [
            'postForm' => Yii::createObject(PostForm::class),
            'route' => $this->route,
            'settings' => Yii::$app->settings->get('common'),
        ]);
    }
}
