<?php

namespace youdate\components;

use app\base\Controller;
use app\traits\DarkModeTrait;
use Yii;
use yii\base\Application;
use yii\base\Event;
use yii\base\BootstrapInterface;
use yii\web\Application as WebApplication;
use youdate\assets\Asset;
use youdate\assets\UploadAsset;
use youdate\controllers\AppearanceController;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\components
 */
class ThemeBootstrap implements BootstrapInterface
{
    use DarkModeTrait;

    /**
     * @var array
     */
    public $rtlLanguages = ['ar', 'az', 'dv', 'he', 'ku', 'fa', 'ur'];

    /**
     * @param Application $app
     * @throws \Exception
     */
    public function bootstrap($app)
    {
        if ($app instanceof WebApplication) {
            $this->bootstrapWeb($app);
        }
    }

    /**
     * @param Application $app
     * @throws \Exception
     */
    protected function bootstrapWeb($app)
    {
        $app->assetManager->bundles[UploadAsset::class] = [
            'sourcePath' => Yii::getAlias('@theme/static'),
            'css' => [],
            'js' => [
                'js/filekit.js',
            ]
        ];

        $darkMode = $this->getDarkMode();
        $allowOverrideDarkMode= $this->allowOverrideDarkMode();
        $app->view->params['site.darkMode'] = $darkMode;
        if ($allowOverrideDarkMode) {
            $app->controllerMap['appearance'] = AppearanceController::class;
            $app->urlManager->addRules([
                'appearance' => 'appearance/index',
            ]);
        }

        // rtl and dark mode
        Yii::$container->set(Asset::class, [
            'rtlEnabled' => $this->isRtlLanguage(),
            'darkMode' => $darkMode,
        ]);

        // use Selectize for MultipleSelect (profile field)
        Yii::$container->set('app\models\fields\MultiSelect', MultiSelect::class);

        Event::on(Controller::class, Controller::EVENT_AFTER_INIT, function (Event $event) {
            /** @var Controller $controller */
            $controller = $event->sender;
            $controller->view->params['rtlEnabled'] = $this->isRtlLanguage();
        });
    }

    /**
     * @return bool
     */
    protected function isRtlLanguage()
    {
        $code = substr(Yii::$app->language, 0, 2);
        return in_array($code, $this->rtlLanguages);
    }
}
