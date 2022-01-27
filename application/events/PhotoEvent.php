<?php

namespace app\events;

use app\models\Photo;
use app\base\Event;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\user\events
 * @property Photo $photo
 */
class PhotoEvent extends Event
{
    /**
     * @var Photo
     */
    private $_photo;

    /**
     * @return Photo
     */
    public function getPhoto()
    {
        return $this->_photo;
    }

    /**
     * @param Photo $photo
     */
    public function setPhoto(Photo $photo)
    {
        $this->_photo = $photo;
    }
}
