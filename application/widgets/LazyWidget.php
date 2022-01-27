<?php

namespace app\widgets;

use app\traits\CacheTrait;
use app\traits\RequestResponseTrait;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\widgets
 */
class LazyWidget extends Widget
{
    use RequestResponseTrait, CacheTrait;

    /**
     * @var string
     */
    public $lazyView;
    /**
     * @var array
     */
    public $lazyParams = [];
    /**
     * @var string
     */
    public $view;
    /**
     * @var array
     */
    public $viewParams = [];
    
    public function run()
    {
        if (!$this->request->isPjax) {
            $lazyParams = $this->lazyParams;
            if (is_callable($lazyParams)) {
                $lazyParams = call_user_func($this->lazyParams);
            }
            return $this->render('lazy/lazy', [
                'id' => $this->getId(),
                'lazyView' => $this->lazyView,
                'lazyParams' => $lazyParams,
            ]);
        }
        
        $viewParams = $this->viewParams;
        if (is_callable($viewParams)) {
            $viewParams = call_user_func($this->viewParams);
        }
        return $this->render('lazy/real', [
            'id' => $this->getId(),
            'view' => $this->view,
            'viewParams' => $viewParams,
        ]);
    }
}
