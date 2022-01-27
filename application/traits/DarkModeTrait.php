<?php

namespace app\traits;

use app\helpers\DarkMode;
use app\settings\UserSettings;
use Yii;

/**
 * @package app\traits
 */
trait DarkModeTrait
{
    use CurrentUserTrait;

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getDarkMode()
    {
        $themeSettingsKey = 'theme.' . Yii::$app->themeManager->getCurrentThemeId();
        $darkMode = Yii::$app->settings->get($themeSettingsKey, 'darkMode', DarkMode::AUTO);

        $user = $this->getCurrentUser();
        if ($user !== null && $this->allowOverrideDarkMode()) {
            $darkMode = UserSettings::forUser($user->id)->getUserSetting('darkMode', $darkMode);
        }

        return $darkMode;
    }

    /**
     * @param $darkMode
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setDarkMode($darkMode)
    {
        if (!in_array($darkMode, DarkMode::$modes)) {
            return false;
        }

        $user = $this->getCurrentUser();
        if ($user !== null && $this->allowOverrideDarkMode()) {
            UserSettings::forUser($user->id)->setUserSetting('darkMode', $darkMode);
            return true;
        }

        return false;
    }


    /**
     * @return array|mixed|null
     * @throws \Exception
     */
    protected function allowOverrideDarkMode()
    {
        $themeSettingsKey = 'theme.' . Yii::$app->themeManager->getCurrentThemeId();

        return Yii::$app->settings->get($themeSettingsKey, 'darkModeUserOverride', true);
    }
}
