<?php

namespace app\modules\admin\components;

use app\modules\admin\helpers\Html;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\components
 */
class AppStatus
{
    const GOOD = 'good';
    const WARNING = 'warning';
    const FAIL = 'fail';

    public static $paths = [
        '@app/runtime',
        '@content/assets',
        '@content/gifts',
        '@content/images',
        '@content/pages',
        '@content/photos',
        '@content/cache',
        '@content/params',
    ];

    /**
     * @return array
     */
    public static function getAll()
    {
        return array_merge(
            self::coreChecks(),
            self::pathsCheck(),
            self::database(),
            self::cronAndQueue()
        );
    }

    /**
     * @return \Closure[]
     */
    public static function coreChecks()
    {
        $checks = [

            'phpCheck' => function() {
                $phpVersion = PHP_VERSION;
                $phpVersionSupportUrl = 'https://www.php.net/supported-versions.php';
                if (version_compare($phpVersion, '7.4', ">=")) {
                    return [
                        'title' => 'You have PHP v' . $phpVersion,
                        'status' => self::GOOD,
                    ];
                }
                if (version_compare($phpVersion, '7.1', ">=")
                    && version_compare($phpVersion, '7.3', "<=")) {
                    return [
                        'title' => Yii::t('app', 'Your PHP version is {0}. It\'s recommended to install 7.4+', $phpVersion),
                        'status' => self::WARNING,
                        'description' => Html::a('See PHP versions support', $phpVersionSupportUrl),
                    ];
                }
                return [
                    'title' => Yii::t('app', 'Your PHP version is outdated (v{0})', $phpVersion),
                    'status' => self::FAIL,
                    'description' => Html::a(Yii::t('app', 'You need at least PHP v7.1. Recommended is v7.4+'), $phpVersionSupportUrl),
                ];
            },

            'imageExtension' => function() {
                $enabled = extension_loaded('gd') || extension_loaded('imagick');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-image-driver.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'GD or Imagick driver enabled') :
                        Yii::t('app', 'GD or Imagick driver is not enabled/installed'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required for image manipulations') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'zipExtension' => function() {
                $enabled = extension_loaded('zip');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-zip-extension.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'ZIP extension enabled') :
                        Yii::t('app', 'ZIP extension is not enabled/installed'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required to generate users personal data archives') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'fileInfoExtension' => function() {
                $enabled = extension_loaded('fileinfo');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-fileinfo-extension.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'Fileinfo extension enabled') :
                        Yii::t('app', 'Fileinfo extension is not enabled/installed'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required to read uploaded files metadata') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'exifExtension' => function() {
                $enabled = extension_loaded('exif');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-exif-extension.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'EXIF extension enabled') :
                        Yii::t('app', 'EXIF extension is not enabled/installed'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required to fix uploaded photos (orientation mode)') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'mbStringExtension' => function() {
                $enabled = extension_loaded('mbstring');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-mbstring-extension.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'MbString extension enabled') :
                        Yii::t('app', 'MbString extension is not enabled/installed'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required by Yii framework') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'intlExtension' => function() {
                $enabled = extension_loaded('intl');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-intl-extension.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'Intl extension enabled') :
                        Yii::t('app', 'Intl extension is not enabled/installed'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required by Yii framework') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'icuExtension' => function() {
                $enabled = defined('INTL_ICU_DATA_VERSION') && version_compare(INTL_ICU_DATA_VERSION, '49.1', '>=');
                $icuVersion = defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : 'none';
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-icu-extension.html';

                if ($icuVersion === null) {
                    $title = Yii::t('app', 'Intl extension is not enabled/installed');
                } elseif (!$enabled) {
                    $title = Yii::t('app', 'Your Intl extension (v{0}) is outdated. v49.1+ is required', $icuVersion);
                } else {
                    $title = Yii::t('app', 'ICU extension enabled');
                }

                return [
                    'title' => $title,
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => !$enabled ?
                        Yii::t('app', 'Required by Yii framework') . '. ' .
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl) : false,
                ];
            },

            'safeModeOff' => function() {
                $enabled = ini_get('safe_mode');
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-safemode.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'Safe mode is on') :
                        Yii::t('app', 'Safe mode is off'),
                    'status' => !$enabled ? self::GOOD : self::FAIL,
                    'description' => $enabled ?
                        Yii::t('app', 'Safe mode? Why?') . '. ' .
                        Html::a(Yii::t('app', 'How to disable?'), $howToUrl) : false,
                ];
            },

        ];

        if (env('APP_MAILER_TRANSPORT') == 'smtp') {
            $checks['smtp'] = function() {
                $enabled = ini_get('SMTP') > 0;
                $howToUrl = 'https://youdate.website/documentation/troubleshooting-smtp.html';
                return [
                    'title' => $enabled ?
                        Yii::t('app', 'SMTP is on') :
                        Yii::t('app', 'SMTP is off'),
                    'status' => $enabled ? self::GOOD : self::FAIL,
                    'description' => $enabled ? '' :
                        Html::a(Yii::t('app', 'How to enable?'), $howToUrl),
                ];
            };
        }

        return $checks;
    }

    /**
     * @return \Closure[]
     */
    public static function pathsCheck()
    {
        return [

            'corePaths' => function () {

                $valid = true;
                $invalidPaths = [];
                foreach (self::$paths as $path) {
                    $path = Yii::getAlias($path);
                    if (!is_writable($path)) {
                        $valid = false;
                        $invalidPaths[] = $path;
                    }
                }

                if ($valid) {
                    return null;
                }

                return [
                    'status' => self::FAIL,
                    'title' => Yii::t('app', 'App is not able to save files in some directories'),
                    'description' => implode(', ', $invalidPaths),
                ];
            },

        ];
    }

    /**
     * @return array
     */
    public static function database()
    {
        $checks = [];
        try {
            $requiredStructure = require (Yii::getAlias('@app/data/foreign-keys.php'));
            $missingFk = [];
            $missingTables = [];
            $tableSchemas = Yii::$app->db->getSchema()->getTableSchemas();
            $tableSchemas = ArrayHelper::index($tableSchemas, 'name');

            foreach ($requiredStructure as $table => $requiredForeignKeys) {
                foreach ($requiredForeignKeys as $requiredFkName => $requiredFk) {
                    if (!isset($tableSchemas[$table])) {
                        $missingTables[] = $table;
                        continue;
                    }
                    $fkNames = $tableSchemas[$table]->foreignKeys;
                    if (!isset($fkNames[$requiredFkName])) {
                        $missingFk[] = $table . '.' . $requiredFkName;
                    }
                }
            }
            $checks['structure'] = function() use ($missingFk, $missingTables) {
                if (count($missingTables) > 0 || count($missingFk) > 0) {
                    $description = Html::a(Yii::t('app', 'How to fix?'), 'https://youdate.website/documentation/troubleshooting-db-structure.html');
                    if (count($missingTables)) {
                        $description .= "<br>" . Yii::t('app', 'Missing database tables') . ':' . '<br>';
                        $description .= implode(', ', array_map(function($table) { return "<code>$table</code>"; }, $missingTables));
                    }
                    if (count($missingFk)) {
                        $description .= "<br>" . Yii::t('app', 'Missing foreign keys') . ':' . '<br>';
                        $description .= implode(', ', array_map(function($fk) { return "<code>$fk</code>"; }, $missingFk));
                    }
                    return [
                        'title' => Yii::t('app', 'Database structure has problems'),
                        'status' => self::FAIL,
                            'description' => $description,
                    ];
                }
                return [
                    'title' => Yii::t('app', 'Database structure is ok'),
                    'status' => self::GOOD,
                ];
            };
        } catch (\Exception $exception) {
            $checks['structure'] = function() use ($exception) {
                return [
                    'title' => Yii::t('app', 'Database structure check'),
                    'status' => self::FAIL,
                    'description' => $exception->getMessage(),
                ];
            };
        }

        return $checks;
    }

    /**
     * @return \Closure[]
     */
    public static function cronAndQueue()
    {
        return [
            'cron' => function() {
                $howToUrl = 'https://youdate.website/documentation/cron.html';
                $lastDailyRun = (int) Yii::$app->settings->get('app', 'cronLastDailyRun');
                $lastHourlyRun = (int) Yii::$app->settings->get('app', 'cronLastHourlyRun');
                $queueSize = (new \yii\db\Query())->from('{{%queue}}')->where('delay = 0')->count();

                $hasFail = false;
                $description = '';
                $formatter = Yii::$app->formatter;

                if (time() - $lastDailyRun > 172800) {
                    $hasFail = true;
                    $description .= '<br> ' . Yii::t('app', 'Daily cron has not run for more than 2 days.');
                    if ($lastDailyRun) {
                        $description .= ' ' . Yii::t('app', 'Last run - {0}', $formatter->asDatetime($lastDailyRun));
                    }
                }
                if (time() - $lastHourlyRun > 7200) {
                    $hasFail = true;
                    $description .= '<br> ' .Yii::t('app', 'Hourly cron has not run for more than 2 hours.');
                    if ($lastHourlyRun) {
                        $description .= ' ' . Yii::t('app', 'Last run - {0}', $formatter->asDatetime($lastHourlyRun));
                    }
                }
                if ($queueSize > 100) {
                    $hasFail = true;
                    $description .= '<br> ' .Yii::t('app', 'Queue has more than 100 jobs. Did you forget to add queue processing?');
                }
                if ($hasFail) {
                    $description = Html::a('How to fix?', $howToUrl) . $description;
                }
                return [
                    'status' => $hasFail ? self::FAIL : self::GOOD,
                    'title' => Yii::t('app', 'Cron and Queue'),
                    'description' => $description,
                ];
            }

        ];
    }

    /**
     * @return mixed
     */
    public static function getStatus()
    {
        return Yii::$app->cache->getOrSet(self::class, function() {
            $hasWarnings = false;
            $hasFails = false;
            foreach (self::getAll() as $check) {
                $item = $check();
                if ($item === null) {
                    continue;
                }
                $hasFails = $hasFails || $item['status'] == self::FAIL;
                $hasWarnings = $hasWarnings || $item['status'] == self::WARNING;
            }
            if ($hasFails) {
                return self::FAIL;
            } elseif ($hasWarnings) {
                return self::WARNING;
            }
            return self::GOOD;
        }, 3600);
    }

    public static function resetStatus()
    {
        Yii::$app->cache->delete(self::class);
    }
}
