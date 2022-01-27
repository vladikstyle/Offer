<?php

namespace app\modules\admin\widgets;

use app\traits\RequestResponseTrait;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\widgets
 */
class AdminSearchWidget extends Widget
{
    use RequestResponseTrait;

    /**
     * @var string
     */
    public $queryParameter = 'q';
    /**
     * @var bool
     */
    public $visible = true;
    /**
     * @var string
     */
    public $viewName = 'admin-search/widget';

    /**
     * @return void|mixed|string
     */
    public function run()
    {
        return $this->render($this->viewName, [
            'queryParameter' => $this->queryParameter,
            'query' => $this->request->get($this->queryParameter),
        ]);
    }
}
