<?php

namespace app\modules\admin\assets;

use yii\web\AssetBundle;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\assets
 */
class UploadAsset extends AssetBundle
{
    public $depends = [
        \yii\web\JqueryAsset::class,
        \trntv\filekit\widget\BlueimpFileuploadAsset::class,
        \trntv\filekit\widget\FontAwesomeAsset::class
    ];

    public $sourcePath = '@app/modules/admin/static/upload';

    public $css = [
        YII_DEBUG ? 'upload-kit.css' : 'upload-kit.min.css'
    ];

    public $js = ['upload-kit.js'];
}
