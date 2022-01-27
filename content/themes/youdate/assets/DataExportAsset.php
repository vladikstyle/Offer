<?php

namespace youdate\assets;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\assets
 */
class DataExportAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@theme/static';
    public $baseUrl = '@web/static';
    public $css = [
        'css/app.min.css',
        'css/data-export.css',
    ];
    public $js = [];
    public $publishOptions = [
        'only' => [
            '*.css',
            '*.otf',
            '*.ttf',
            '*.woff',
            '*.woff2',
            'logo@2x.png',
        ],
    ];
}
