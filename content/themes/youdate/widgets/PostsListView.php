<?php

namespace youdate\widgets;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class PostsListView extends ListView
{
    public function init()
    {
        parent::init();
        $this->options['class'] = 'list-view posts-list-view';
    }
}
