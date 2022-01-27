<?php

namespace app\commands;

use app\components\AppState;
use app\components\ConsoleRunner;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\commands
 */
class UpdateController extends \app\base\Command
{
    /**
     * @throws \Exception
     */
    public function actionApply()
    {
        $consoleRunner = new ConsoleRunner();
        $appState = new AppState();
        $appState->readState();

        $appState->setMaintenance(true);
        $consoleRunner->run('migrate/up', ['interactive' => 0]);
        $consoleRunner->run('cache/flush-all');

        $appState->setMaintenance(false);
        $appState->updateVersion();
    }
}
