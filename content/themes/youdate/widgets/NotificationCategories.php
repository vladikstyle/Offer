<?php

namespace youdate\widgets;

use app\notifications\BaseNotificationCategory;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class NotificationCategories extends Widget
{
    /**
     * @var string
     */
    public $view = 'notifications/categories';
    /**
     * @var BaseNotificationCategory[]
     */
    public $categories = [];
    /**
     * @var string
     */
    public $filters;

    /**
     * @return string
     */
    public function run()
    {
        if (!empty($this->filters)) {
            $filters = explode(',', $this->filters);
        } else {
            $filters = [];
        }

        return $this->render($this->view, [
            'categories' => $this->categories,
            'filters' => $filters,
        ]);
    }
}
