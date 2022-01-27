<?php

namespace app\settings;

use app\traits\RequestResponseTrait;
use app\traits\SessionTrait;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\settings
 */
class SettingsAction extends \yii\base\Action
{
    use RequestResponseTrait, SessionTrait;

    /**
     * @var string
     */
    public $category;
    /**
     * @var string
     */
    public $viewFile;
    /**
     * @var array|callable
     */
    public $viewParams = [];
    /**
     * @var array
     */
    public $items;
    /**
     * @var string
     */
    public $title;

    /**
     * @return string
     * @throws \Exception
     */
    public function run()
    {
        $settingsManager = new SettingsManager($this->category, $this->items);
        $settingsModel = SettingsModel::createModel($this->items);
        $settingsModel->setAttributes($settingsManager->getSetting($settingsModel->getAttributes()));

        if ($settingsModel->load($this->request->post()) && $settingsModel->validate()) {
            $settingsManager->setSetting($settingsModel->getAttributes());
            $this->session->setFlash('settings', 'Settings have been saved');
            return $this->controller->refresh();
        }

        $viewParams = is_callable($this->viewParams) ? call_user_func($this->viewParams) : $this->viewParams;
        $viewParams = array_merge($viewParams, [
            'settingsManager' => $settingsManager,
            'settingsModel' => $settingsModel,
            'items' => $this->items,
            'title' => $this->title,
        ]);

        return $this->controller->render($this->viewFile, $viewParams);
    }
}
