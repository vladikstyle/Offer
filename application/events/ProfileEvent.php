<?php

namespace app\events;

use app\models\Profile;
use app\base\Event;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\events
 * @property Profile $profile
 */
class ProfileEvent extends Event
{
    /**
     * @var Profile
     */
    private $_profile;

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * @param Profile $profile
     */
    public function setProfile(Profile $profile)
    {
        $this->_profile = $profile;
    }
}
