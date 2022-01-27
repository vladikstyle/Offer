<?php

namespace app\modules\admin\widgets;

use conquer\select2\Select2Widget;
use yii\web\JsExpression;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\widgets
 */
class UserSearch extends Select2Widget
{
    /**
     * @var array
     */
    public $ajax = ['user/ajax'];
    /**
     * @var array
     */
    public $initSelection;
    /**
     * @var string
     */
    public $templateResult;
    /**
     * @var string
     */
    public $templateSelection;

    public function init()
    {
        parent::init();

        $this->templateResult = new JsExpression('function(item) {
            if (!item.id) {
                return "";
            }
            var html = $(\'<div class="user-filter hidden"><img class="avatar" /><div class="info"><div class="name"></div><div class="username"></div></div></div>\');
            html.find(".name").text(item.name);
            html.find(".username").text(item.username);
            html.find("img").attr("src", item.avatar);
            html.removeClass("hidden");
            return html;
        }');

        $this->templateSelection = new JsExpression('function(item) {
            if (!item.id) {
                return "";
            }
            var html = $(\'<div><span class="username"></span><button class="btn btn-xs btn-warning reset pull-right select2-clear"><i class="fa fa-trash"></i></button></div>\');
            html.find(".username").text(item.text);
            html.removeClass("hidden");

            return html;
        }');

        $this->settings = array_merge($this->settings, [
            'delay' => 250,
            'minimumInputLength' => 1,
            'width' => '100%',
            'templateResult' => $this->templateResult,
            'templateSelection' => $this->templateSelection,
        ]);

        if (isset($this->initSelection)) {
            $this->settings['initSelection'] = new JsExpression('function (element, callback) {
                var data = [];
                data.push(' . json_encode($this->initSelection) . ');
                callback(data);
            }');
        }
    }
}
