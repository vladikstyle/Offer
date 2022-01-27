<?php

namespace app\traits;

/**
 * @package app\traits
 */
trait CaptchaRequired
{
    use SettingsTrait;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function isCaptchaRequired()
    {
        return $this->settings->get('frontend', 'siteRequireCaptcha', false);
    }
}
