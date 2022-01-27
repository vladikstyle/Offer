<?php

namespace app\commands;

use Yii;
use yii\helpers\FileHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\commands
 */
class OptimizeController extends \app\base\Command
{
    /**
     * @var string
     */
    public $unusedDependencies = '@app/data/unused-dependencies.php';

    /**
     * Run all available optimizations
     *
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     */
    public function actionIndex()
    {
        $this->runAction('unused-dependencies');
    }

    /**
     * @throws \yii\base\ErrorException
     */
    public function actionUnusedDependencies()
    {
        $paths = [];
        $unusedDependencies = require(Yii::getAlias($this->unusedDependencies));

        foreach ($unusedDependencies as $dependency) {
            $paths[] = Yii::getAlias($dependency);
        }

        $this->stdout("- Removing unused dependencies:\n");
        foreach ($paths as $path) {
            if (is_dir($path)) {
                FileHelper::removeDirectory($path);
                $this->stdout("Removed directory: $path\n");
            }
            if (is_file($path)) {
                FileHelper::unlink($path);
                $this->stdout("Removed file: $path\n");
            }
        }
    }
}
