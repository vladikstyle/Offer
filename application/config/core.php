<?php

use app\settings\LazySettingsValue;

$config = [
    'id' => 'youdate',
    'name' => 'YouDate',
    'language' => 'en-US',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'bootstrap' => [
        'queue',
    ],
    'modules' => [],
    'components' => [
        // framework related
        'assetManager' => [
            'forceCopy' => env('APP_DEBUG'),
            'appendTimestamp' => true,
            'basePath' => '@webroot/content/assets',
            'baseUrl' => '@web/content/assets',
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'mutex' => [
            'class' => yii\mutex\MysqlMutex::class,
        ],
        'queue' => [
            'class' => yii\queue\db\Queue::class,
            'as log' => yii\queue\LogBehavior::class,
        ],
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => yii\i18n\DbMessageSource::class,
                    'sourceLanguage' => 'en-US',
                    'sourceMessageTable' => '{{%language_source}}',
                    'messageTable' => '{{%language_translate}}',
                    'cachingDuration' => 86400,
                    'enableCaching' => !YII_DEBUG,
                    'forceTranslation' => true,
                ],
                '*' => [
                    'class' => yii\i18n\DbMessageSource::class,
                    'sourceLanguage' => 'en-US',
                    'sourceMessageTable' => '{{%language_source}}',
                    'messageTable' => '{{%language_translate}}',
                    'cachingDuration' => 86400,
                    'enableCaching' => !YII_DEBUG,
                    'forceTranslation' => true,
                ]
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'except' => [
                        'app\components\AppException',
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:401',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                    ],
                ],
                [
                    'class' => yii\log\DbTarget::class,
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'except' => [
                        'app\components\AppException',
                        'yii\console\UnknownCommandException',
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:401',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:405',
                    ],
                ],
            ],
        ],
        'urlManager' => [
            'class' => app\components\url\UrlManager::class,
            'ruleConfig' => ['class' => app\components\url\UrlRule::class],
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require(__DIR__ . '/routes.php'),
        ],

        // app related
        'appMailer' => [
            'class' => app\components\AppMailer::class,
        ],
        'glide' => [
            'class' => app\components\Glide::class,
            'sourcePath' => '@content/photos',
            'cachePath' => '@content/cache',
            'cacheUrl' => '@web/content/cache',
            'signKey' => env('APP_GLIDE_KEY'),
        ],
        'photoStorage' =>[
            'class' => app\files\Storage::class,
            'baseUrl' => '@web/content/photos',
            'path' => '@content/photos',
        ],
        'geographer' => [
            'class' => app\helpers\Geographer::class,
            'source' => env('APP_GEODATA_SOURCE', 'geographer'),
        ],
        'emoji' => [
            'class' => app\helpers\Emoji::class,
        ],
        'settings' => [
            'class' => app\settings\Settings::class,
            'preLoad' => ['frontend', 'backend', 'theme'],
        ],
        'balanceManager' => [
            'class' => app\managers\BalanceManager::class,
            'accountClass' => app\models\Balance::class,
            'transactionClass' => app\models\BalanceTransaction::class,
            'accountLinkAttribute' => 'user_id',
            'extraAccountLinkAttribute' => 'user_id',
            'amountAttribute' => 'amount',
            'dataAttribute' => 'data',
            'accountBalanceAttribute' => 'balance',
        ],
        'themeManager' => [
            'class' => app\themes\ThemeManager::class,
        ],
        'pluginManager' => [
            'class' => app\plugins\PluginManager::class,
        ],
        'pluginInstaller' => [
            'class' => app\plugins\PluginInstaller::class,
            'baseUrl' => env('APP_PLUGINS_URL', 'https://youdate.hauntd.me/plugins/api'),
            'licenseKey' => new LazySettingsValue('common', 'appLicenseKey', env('APP_LICENSE_KEY')),
        ],
        'userManager' => [
            'class' => app\managers\UserManager::class,
        ],
        'groupManager' => [
            'class' => app\managers\GroupManager::class,
        ],
        'photoManager' => [
            'class' => app\managers\PhotoManager::class,
        ],
        'likeManager' => [
            'class' => app\managers\LikeManager::class,
        ],
        'guestManager' => [
            'class' => app\managers\GuestManager::class,
        ],
        'messageManager' => [
            'class' => app\managers\MessageManager::class,
        ],
        'notificationManager' => [
            'class' => app\managers\NotificationManager:: class,
            'notificationClasses' => [
                app\notifications\ProfileView::class,
                app\notifications\ProfileLike::class,
                app\notifications\GiftReceived::class,
                app\notifications\PhotoAccessRequest::class,
                app\notifications\PhotoAccessAction::class,
            ]
        ],
        'giftManager' => [
            'class' => app\managers\GiftManager::class,
            'giftsDirectory' => '@content/gifts',
            'giftsUrl' => '@web/content/gifts',
        ],
        'dataExportManager' => [
            'class' => app\components\data\DataExportManager::class,
        ],
    ],
    'params' => [
        'autoApplyUpdates' => true,
        'settings' => require(__DIR__ . '/settings.php'),
        'onlineThreshold' => 600, // 10 minutes
        'guestVisitThreshold' => 6000, // 60 minutes
        'expiredEncountersThreshold' => 30, // 30 days
        'expiredNotificationsThreshold' => 30, // 30 days,
        'expiredDataRequestsThreshold' => 3, // 3 days,
        'maxDataExportsPerDay' => 3,
    ]
];

$mailerConfig = [
    'class' => yii\swiftmailer\Mailer::class,
    'viewPath' => '@app/views/mail',
];
switch (env('APP_MAILER_TRANSPORT')) {
    case 'mail':
        $config['components']['mailer'] = array_merge($mailerConfig, [
            'transport' => [
                'class' => \app\components\swiftmailer\SwiftMail::class,
            ],
        ]);
        break;
    /**
     * Sendmail
     */
    case 'sendmail';
        $config['components']['mailer'] = array_merge($mailerConfig, [
            'transport' => [
                'class' => 'Swift_SendmailTransport',
            ],
        ]);
        break;
    /**
     * File transport
     */
    case 'file':
        $config['components']['mailer'] = array_merge($mailerConfig, ['useFileTransport' => true]);
        break;
    /**
     * SMTP
     */
    case 'smtp':
        $config['components']['mailer'] = array_merge($mailerConfig, [
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => env('APP_MAILER_HOST'),
                'username' => env('APP_MAILER_USERNAME'),
                'password' => env('APP_MAILER_PASSWORD'),
                'port' => env('APP_MAILER_PORT'),
                'encryption' => env('APP_MAILER_ENCRYPTION'),
            ],
        ]);
        if (env('APP_MAILER_VERIFY_SSL', true) == false) {
            $config['components']['mailer']['transport']['streamOptions'] = [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ];
        }
        break;
}

return $config;
