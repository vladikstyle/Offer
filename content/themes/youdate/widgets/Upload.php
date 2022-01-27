<?php

namespace youdate\widgets;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\jui\JuiAsset;
use youdate\assets\UploadAsset;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class Upload extends \trntv\filekit\widget\Upload
{
    /**
     * @var array
     */
    public $wrapperOptions = [];
    /**
     * @var array
     */
    public $buttonsWrapperOptions = [];
    /**
     * @var string
     */
    public $buttonsPrependHtml = '';
    /**
     * @var string
     */
    public $buttonsAppendHtml = '';
    /**
     * @var string
     */
    public $filesHtml;

    public function init()
    {
        parent::init();
        $this->clientOptions = array_merge($this->clientOptions, [
            'errorHandler' => 'yii',
            'fail' => new \yii\web\JsExpression('function(event, data) {
                if (data.message) {
                    Messenger().post({ errorMessage: data.message });
                }
            }')
        ]);
    }

    public function registerClientScript()
    {
        UploadAsset::register($this->getView());
        $options = Json::encode($this->clientOptions);
        if ($this->sortable) {
            JuiAsset::register($this->getView());
        }
        $this->getView()->registerJs("jQuery('#{$this->getId()}').yiiUploadKit({$options});");
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->registerClientScript();
        Html::addCssClass($this->wrapperOptions, 'upload-kit');
        $content = Html::beginTag('div', $this->wrapperOptions);
        $content .= Html::hiddenInput($this->name, null, [
            'class' => 'empty-value',
            'id' => $this->hiddenInputId === null ? $this->options['id'] : $this->hiddenInputId
        ]);
        if (!empty($this->filesHtml)) {
            $content .= $this->filesHtml;
        }
        $content .= Html::beginTag('div', $this->buttonsWrapperOptions);
        if (!empty($this->buttonsPrependHtml)) {
            $content .= $this->buttonsPrependHtml;
        }
        $content .= Html::fileInput($this->getFileInputName(), null, [
            'name' => $this->getFileInputName(),
            'id' => $this->getId(),
            'multiple' => $this->multiple
        ]);
        if (!empty($this->buttonsAppendHtml)) {
            $content .= $this->buttonsAppendHtml;
        }
        $content .= Html::endTag('div'); // buttons
        $content .= Html::endTag('div'); // widget

        return $content;
    }
}
