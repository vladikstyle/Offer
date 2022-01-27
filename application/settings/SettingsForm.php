<?php

namespace app\settings;

use app\traits\RequestResponseTrait;
use yii\base\Exception;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\settings
 */
class SettingsForm extends Widget
{
    use RequestResponseTrait;

    /**
     * @var SettingsManager Configured manager
     */
    public $manager;
    /**
     * @var string
     */
    public $formView;
    /**
     * @var array
     */
    public $viewParams = [];
    /**
     * @var SettingsModel Helper dynamic model
     */
    public $model;
    /**
     * @var array
     */
    public $elements = [];
    /**
     * @var
     */
    public $title;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->model->items as $element) {
            $alias = $element['alias'];
            $element['type'] = isset($element['type']) ? $element['type'] : 'text';
            $element['help'] = isset($element['help']) ? $element['help'] : null;
            if (in_array($element['type'], ['dropdown', 'checkboxList']) && empty($element['options'])) {
                throw new Exception('Input type ' . $element['type'] . ' requires `options` property');
            }
            if (isset($element['default'])) {
                $this->model->{$alias} = $element['default'];
            }
            $this->elements[$alias] = $element;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->request->isAjax) {
            return $this->getView()->renderAjax($this->formView, [
                'model' => $this->model,
                'elements' => $this->elements,
                'title' => $this->title,
            ], $this);
        }

        $viewParams = array_merge([
            'model' => $this->model,
            'elements' => $this->elements,
            'title' => $this->title,
        ], $this->viewParams);

        return $this->render($this->formView, $viewParams);
    }
}
