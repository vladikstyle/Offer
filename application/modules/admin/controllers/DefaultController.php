<?php

namespace app\modules\admin\controllers;

use app\actions\UploadAction;
use app\helpers\Url;
use app\models\Admin;
use app\modules\admin\actions\ErrorAction;
use app\modules\admin\components\Charts;
use app\modules\admin\components\Permission;
use app\settings\Settings;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class DefaultController extends \app\modules\admin\components\Controller
{
    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        return array_merge(parent::actions(), [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'upload-photo' => [
                'class' => UploadAction::class,
                'fileStorage' => 'photoStorage',
                'deleteRoute' => Url::to(['upload-photo-delete']),
                'multiple' => true,
                'disableCsrf' => true,
                'maxWidth' => $this->settings->get('common', 'photoMaxWidth', 1500),
                'maxHeight' => $this->settings->get('common', 'photoMaxHeight', 1500),
                'validationRules' => [
                    [
                        'file', 'image',
                        'extensions' => ['jpg', 'jpeg', 'tiff', 'png', 'gif'],
                    ],
                ],
            ],
            'upload-photo-delete' => [
                'class' => \trntv\filekit\actions\DeleteAction::class,
                'fileStorage' => 'photoStorage',
            ]
        ]);
    }

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'except' => ['error'],
            ],
        ]);
    }

    /**
     * @return string|void
     * @throws \Exception
     */
    public function actionIndex()
    {
        if ($this->getCurrentUser()->isModerator) {
            return $this->indexModerator();
        }

        return $this->indexAdmin();
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function indexModerator()
    {
        return $this->render('index-moderator', [
            'counters' => $this->stats->getCounters(),
            'photoModerationEnabled' => $this->settings->get('common', 'photoModerationEnabled'),
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function indexAdmin()
    {
        /** @var Settings $settings */
        $settings = Yii::$app->settings;
        $charts = new Charts();

        $trim = function ($version) {
            $parts = explode('-', $version);
            if (count($parts)) {
                return $parts[0];
            }
            return $version;
        };

        return $this->render('index-admin', [
            'counters' => $this->stats->getCounters(),
            'info' => [
                'version' => version(),
                'frameworkVersion' => Yii::getVersion(),
                'phpVersion' => $trim(phpversion()),
                'phpVersionOutdated' => version_compare($trim(phpversion()), '7.2', '<'),
                'mysqlVersion' => $trim(Yii::$app->db->createCommand('select version()')->queryScalar()),
                'debug' => env('APP_DEBUG'),
                'environment' => env('APP_ENV'),
                'cronHourly' => (int) $settings->get('app', 'cronLastHourlyRun'),
                'cronDaily' => (int) $settings->get('app', 'cronLastDailyRun'),
                'queueSize' => (new \yii\db\Query())->from('{{%queue}}')->count(),
                'memoryLimit' => ini_get('memory_limit'),
                'timeLimit' => ini_get('max_execution_time'),
                'uploadMaxFilesize' => ini_get('upload_max_filesize'),
                'postMaxSize' => ini_get('post_max_size'),
            ],
            'charts' => [
                'dailyLabels' => $charts->getDailyLabels(),
                'newUsersData' => $charts->getUsersData(),
                'profitData' => $charts->getProfitData(),
            ]
        ]);
    }

    /**
     * @param null $country
     * @param $query
     * @throws \yii\base\ExitException
     */
    public function actionFindCities($country, $query)
    {
        $this->sendJson(['cities' =>  Yii::$app->geographer->findCities($country, $query)]);
    }
}
