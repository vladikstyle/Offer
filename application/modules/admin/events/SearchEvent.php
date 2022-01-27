<?php

namespace app\modules\admin\events;

use app\base\Event;
use app\modules\admin\components\Search;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\events
 * @property Search $sender
 */
class SearchEvent extends Event
{
    /**
     * @var string
     */
    public $searchQuery;
    /**
     * @var array
     */
    public $searchProviders;
    /**
     * @var array
     */
    public $searchResults;
}
