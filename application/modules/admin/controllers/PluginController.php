<?php

namespace app\modules\admin\controllers;

use app\components\AppException;
use app\settings\HasSettings;
use app\settings\SettingsAction;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class PluginController extends \app\modules\admin\components\Controller
{
    /**
     * @var string
     */
    public $layout = '@app/modules/admin/views/plugin/_layout.php';

    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        $pluginId = $this->request->get('pluginId');
        $plugins = Yii::$app->pluginManager->getEnabledPlugins();

        $actions = [];
        if (isset($plugins[$pluginId]) && $plugins[$pluginId] instanceof HasSettings) {
            $plugin = $plugins[$pluginId];
            $actions['settings'] = [
                'class' => SettingsAction::class,
                'category' => "plugin.$pluginId",
                'title' => Yii::t('app', 'Plugin settings'),
                'viewFile' => 'settings',
                'viewParams' => [
                    'plugin' => $plugin,
                ],
                'items' => $plugin->getSettings(),
            ];
        }

        return $actions;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'plugins' => Yii::$app->pluginManager->getAvailablePlugins(),
        ]);
    }

    /**
     * @param null $searchQuery
     * @return string
     */
    public function actionBrowse($searchQuery = null)
    {
        try {
            $pluginDataProvider = Yii::$app->pluginInstaller->getPluginsProvider($searchQuery);
        } catch (AppException $exception) {
            $this->session->setFlash($exception->level, $exception->getMessage());
            $pluginDataProvider = new ArrayDataProvider(['allModels' => []]);
        } catch (\Exception $exception) {
            $this->session->setFlash('error', $exception->getMessage());
            $pluginDataProvider = new ArrayDataProvider(['allModels' => []]);
        }

        return $this->render('browse', [
            'pluginDataProvider' => $pluginDataProvider,
            'searchQuery' => $searchQuery,
            'installedPlugins' => Yii::$app->pluginManager->getAvailablePlugins(),
            'enabledPlugins' => Yii::$app->pluginManager->getEnabledPlugins(),
            'zipExtensionLoaded' => extension_loaded('zip'),
        ]);
    }

    /**
     * @param $pluginId
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionEnable($pluginId)
    {
        try {
            if (Yii::$app->pluginManager->enablePlugin($pluginId)) {
                $this->session->setFlash('success', Yii::t('app', 'Plugin has been enabled'));
            }
        } catch (AppException $appException) {
            $this->session->setFlash($appException->level, $appException->getMessage());
        }

        return $this->redirect($this->request->referrer);
    }

    /**
     * @param $pluginId
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionDisable($pluginId)
    {
        if (Yii::$app->pluginManager->disablePlugin($pluginId)) {
            Yii::$app->settings->remove("plugin.$pluginId");
            $this->session->setFlash('success', Yii::t('app', 'Plugin has been disabled'));
        }

        return $this->redirect($this->request->referrer);
    }

    /**
     * @param $pluginId
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionInstall($pluginId)
    {
        try {
            Yii::$app->pluginInstaller->installPlugin($pluginId);
            $this->session->setFlash('success', Yii::t('app', 'Plugin has been installed'));
        } catch (AppException $exception) {
            $this->session->setFlash($exception->level, $exception->getMessage());
        } finally {
            Yii::$app->pluginInstaller->cleanUp();
        }

        return $this->redirect($this->request->referrer);
    }

    /**
     * @param $pluginId
     * @return \yii\web\Response
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function actionUpdate($pluginId)
    {
        try {
            Yii::$app->pluginInstaller->updatePlugin($pluginId);
            $this->session->setFlash('success', Yii::t('app', 'Plugin has been updated'));
        } catch (AppException $exception) {
            $this->session->setFlash($exception->level, $exception->getMessage());
        } finally {
            Yii::$app->pluginInstaller->cleanUp();
        }

        return $this->redirect($this->request->referrer);
    }

    /**
     * @param $pluginId
     * @return \yii\web\Response
     * @throws \yii\base\ErrorException
     */
    public function actionUninstall($pluginId)
    {
        try {
            Yii::$app->pluginInstaller->unInstallPlugin($pluginId);
            $this->session->setFlash('success', Yii::t('app', 'Plugin has been uninstalled'));
        } catch (AppException $exception) {
            $this->session->setFlash($exception->level, $exception->getMessage());
        } finally {
            Yii::$app->pluginInstaller->cleanUp();
        }

        return $this->redirect($this->request->referrer);
    }
}
