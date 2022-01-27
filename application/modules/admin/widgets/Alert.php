<?php

namespace app\modules\admin\widgets;

use app\traits\SessionTrait;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\widgets
 */
class Alert extends \yii\bootstrap\Widget
{
    use SessionTrait;

    /**
     * @var array the alert types configuration for the flash messages.
     *            This array is setup as $key => $value, where:
     *            - $key is the name of the session flash variable
     *            - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error' => 'alert-danger',
        'danger' => 'alert-danger',
        'success' => 'alert-success',
        'info' => 'alert-info',
        'warning' => 'alert-warning',
    ];
    /**
     * @var array the options for rendering the close button tag.
     */
    public $closeButton = [];

    public function init()
    {
        parent::init();
        $flashes = $this->session->getAllFlashes();
        $appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
        foreach ($flashes as $type => $data) {
            if (isset($this->alertTypes[$type])) {
                $data = (array)$data;
                foreach ($data as $message) {
                    /* initialize css class for each alert box */
                    $this->options['class'] = $this->alertTypes[$type] . $appendCss;
                    /* assign unique id to each alert box */
                    $this->options['id'] = $this->getId() . '-' . $type;
                    echo \yii\bootstrap\Alert::widget(
                        [
                            'body' => $message,
                            'closeButton' => $this->closeButton,
                            'options' => $this->options,
                        ]
                    );
                }
                $this->session->removeFlash($type);
            }
        }
    }
}
