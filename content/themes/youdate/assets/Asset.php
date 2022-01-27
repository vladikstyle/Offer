<?php

namespace youdate\assets;

use app\helpers\DarkMode;
use app\traits\DarkModeTrait;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\assets
 */
class Asset extends \yii\web\AssetBundle
{
    /**
     * @var string
     */
    public $basePath = '@theme/static';
    /**
     * @var string
     */
    public $baseUrl = '@themeUrl/static';
    /**
     * @var bool
     */
    public $rtlEnabled = false;
    /**
     * @var string
     */
    public $darkMode = DarkMode::AUTO;
    /**
     * @var array
     */
    public $js = [
        'js/app.js',
    ];
    public $depends = [
        CoreAsset::class,
    ];

    public function init()
    {
        parent::init();
        if ($this->rtlEnabled == true) {
            $this->css[] = 'css/app.rtl.min.css';
        } else {
            $this->css[] = 'css/app.min.css';
        }

        if ($this->darkMode === DarkMode::ALWAYS_DARK) {
            $this->css[] = 'css/app-dark.min.css';
        } elseif ($this->darkMode === DarkMode::AUTO) {
            $this->css[] = 'css/app-auto.min.css';
        }
    }
}
