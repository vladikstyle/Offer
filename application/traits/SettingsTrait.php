<?php

namespace app\traits;

use app\settings\Settings;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits
 * @property Settings $settings
 */
trait SettingsTrait
{
    /**
     * @var string
     */
    protected $settingsComponent = 'settings';
    /**
     * @var Settings
     */
    protected $settingsComponentCached;

    /**
     * @return Settings|null|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettings()
    {
        if (!isset($this->settingsComponentCached)) {
            $this->settingsComponentCached = Yii::$app->get($this->settingsComponent);
        }

        return $this->settingsComponentCached;
    }
}
