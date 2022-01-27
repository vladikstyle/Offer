<?php

namespace app\modules\admin\widgets;

use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\widgets
 */
class AdminQuickSearchResults extends Widget
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var array
     */
    public $results = [];
    /**
     * @var string
     */
    public $fallback;
    /**
     * @var string
     */
    public $viewName = 'admin-search/results';

    /**
     * @return void|mixed|string
     */
    public function run()
    {
        return $this->render($this->viewName, [
            'title' => $this->title,
            'results' => is_array($this->results) ? $this->results : [$this->results],
            'fallback' => $this->fallback,
        ]);
    }
}
