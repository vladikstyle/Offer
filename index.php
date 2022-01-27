<?php

// Fix broken HTTPS detection on some cases
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['X-Forwarded-Proto']) && $headers['X-Forwarded-Proto'] == 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
}

require(__DIR__ . '/application/bootstrap.php');
require(__DIR__ . '/application/vendor/autoload.php');
require(__DIR__ . '/application/environment.php');
require(__DIR__ . '/application/vendor/yiisoft/yii2/Yii.php');

if (isInstalled()) {
    $config = require(__DIR__ . '/application/config/web.php');
} else {
    $config = require(__DIR__) . '/application/installer/config/installer.php';
    $config['params']['basePath'] = __DIR__;
}

$application = new yii\web\Application($config);
$application->run();
