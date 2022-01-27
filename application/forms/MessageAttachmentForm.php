<?php

namespace app\forms;

use app\models\User;
use app\settings\Settings;
use Yii;
use yii\web\Application as WebApplication;
use League\Flysystem\File;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\forms
 */
class MessageAttachmentForm extends MessageForm
{
    /**
     * @var File
     */
    public $files;

    /**
     * @return array
     * @throws \Exception
     */
    public function rules()
    {
        /** @var Settings $settings */
        $settings = Yii::$app->settings;
        $sizeMultiplier = 1024 * 1024;

        $rules = [
            [['contactId', 'files'], 'required'],
            ['contactId', 'exist',
                'targetClass' => User::class,
                'targetAttribute' => ['contactId' => 'id']
            ],
            [
                'files', 'image',
                'minFiles' => 1,
                'maxFiles' => 5,
                'maxSize' => $settings->get('common', 'photoMaxFileSize', $sizeMultiplier * 10) * $sizeMultiplier,
                'extensions' => ['jpg', 'jpeg', 'tiff', 'png', 'gif']
            ]
        ];

        if (Yii::$app instanceof WebApplication) {
            $rules[] = ['contactId', 'compare', 'compareValue' => Yii::$app->user->id, 'operator' => '!='];
            $rules[] = ['contactId', 'checkBlock'];
        }

        return $rules;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
