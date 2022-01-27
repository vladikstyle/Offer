<?php

namespace youdate\widgets;

use Yii;
use app\models\Group;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class GroupPostsListView extends PostsListView
{
    /**
     * @var Group
     */
    public $group;

    public function init()
    {
        $this->dataProvider = Yii::$app->groupManager->getPostsDataProvider($this->group);

        parent::init();
    }
}
