<?php

namespace app\behaviors;

use app\helpers\GUID;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\base\Behavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\behaviors
 *
 * @property $owner ActiveRecord
 */
class GuidBehavior extends Behavior
{
    /**
     * @var string
     */
    public $attribute = 'guid';

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'setGuid',
            ActiveRecord::EVENT_BEFORE_INSERT => 'setGuid',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setGuid',
        ];
    }

    /**
     * @param $event Event
     * @throws \yii\base\Exception
     */
    public function setGuid($event)
    {
        if ($this->owner->isNewRecord) {
            if ($this->owner->{$this->attribute} == "") {
                $this->owner->{$this->attribute} = GUID::generate();
            }
        }
    }
}
