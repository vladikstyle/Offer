<?php

namespace app\components;

use Yii;
use yii\helpers\FileHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components
 */
class Maintenance
{
    public static function flushCache()
    {
        // flush cache
        Yii::$app->cache->flush();

        // remove published assets
        $path = Yii::getAlias(Yii::$app->assetManager->basePath);
        foreach (FileHelper::findDirectories($path) as $directory) {
            if (count(glob("$directory/*")) === 0) {
                FileHelper::removeDirectory($directory);
            }
        }
    }

    public static function flushThumbnails()
    {
        try {
            $directories = FileHelper::findDirectories(Yii::getAlias('@content/cache'));
            foreach ($directories as $directory) {
                FileHelper::removeDirectory($directory);
            }
        } catch (\Exception $exception) {
            Yii::error($exception->getMessage());
        }
    }
}
