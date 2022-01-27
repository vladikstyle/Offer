<?php

namespace youdate\widgets;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class GroupsListView extends ListView
{
    public function init()
    {
        parent::init();
        $this->options['class'] = 'list-view groups-list-view';
        $this->layout = '<div class="row row-cards row-deck">{items}</div><div class="pager">{pager}</div>';
    }
}
