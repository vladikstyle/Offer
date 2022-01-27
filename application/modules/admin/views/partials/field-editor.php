<?php

use app\helpers\Common;
use app\helpers\Url;
use yii\web\JsExpression;
use dosamigos\tinymce\TinyMce;

/** @var $form \yii\widgets\ActiveForm */
/** @var $model \yii\base\Model */
/** @var $attribute string */
/** @var $label string|null */

$imageUploadUrl = Url::to(['default/upload-photo']);

echo $form->field($model, $attribute)->widget(TinyMce::class, [
    'options' => ['rows' => 20],
    'language' => Common::getShortLanguage(Yii::$app->language),
    'clientOptions' => [
        'plugins' => [
            'advlist autolink lists link charmap print preview anchor image',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste',
        ],
        'toolbar' => 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        'images_upload_url' => $imageUploadUrl,
        'images_upload_handler' => new JsExpression('function (blobInfo, success, failure) {
                var xhr, formData;

                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open("POST", "'. $imageUploadUrl . '");
            
                xhr.onload = function() {
                    var json;
                    if (xhr.status != 200) {
                        failure(\'HTTP Error: \' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    console.log(json);
                    
                    if (!json || !json.files || !json.files[0]) {
                        failure(\'Invalid JSON: \' + xhr.responseText);
                        return;
                    }
                    success(json.files[0].url);
                };
            
                formData = new FormData();
                formData.append("file", blobInfo.blob(), blobInfo.filename());
            
                xhr.send(formData);
            }'),
    ]
])->label($label ?? $model->getAttributeLabel($attribute));
