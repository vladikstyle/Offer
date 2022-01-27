<?php

namespace app\actions;

use app\base\Controller;
use app\traits\CurrentUserTrait;
use app\traits\managers\PhotoManagerTrait;
use app\traits\SessionTrait;
use Imagine\Filter\Basic\Autorotate;
use Yii;
use yii\web\UploadedFile;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\base\DynamicModel;
use yii\imagine\Image;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\actions
 * @property Controller $controller
 */
class UploadAction extends \trntv\filekit\actions\UploadAction
{
    use SessionTrait, PhotoManagerTrait, CurrentUserTrait;

    const EVENT_AFTER_VALIDATION = 'afterValidation';

    /**
     * @var int|null
     */
    public $maxWidth;
    /**
     * @var int|null
     */
    public $maxHeight;
    /**
     * @var int
     */
    public $quality = 90;
    /**
     * @var array
     */
    public $modelAttributes = [];

    public function run()
    {
        $result = [];
        $uploadedFiles = UploadedFile::getInstancesByName($this->fileparam);

        foreach ($uploadedFiles as $uploadedFile) {
            /* @var \yii\web\UploadedFile $uploadedFile */
            $output = [
                $this->responseNameParam => Html::encode($uploadedFile->name),
                $this->responseMimeTypeParam => $uploadedFile->type,
                $this->responseSizeParam => $uploadedFile->size,
                $this->responseBaseUrlParam =>  $this->getFileStorage()->baseUrl
            ];
            if ($uploadedFile->error === UPLOAD_ERR_OK) {
                $attributes = array_merge(['file' => $uploadedFile], $this->modelAttributes);
                $validationModel = DynamicModel::validateData($attributes, $this->validationRules);
                if (!$validationModel->hasErrors()) {
                    $path = $this->getFileStorage()->save($uploadedFile);
                    $absolutePath = $this->getFileStorage()->getAbsolutePath($path);

                    try {
                        $imagine = Image::getImagine();
                        $image = $imagine->open($absolutePath);
                        // apply auto-rotate
                        $image = (new Autorotate('000000'))->apply($image);
                        // remove meta-data
                        $image->strip();
                        // resize (if needed)
                        $imageSize = $originalSize = $image->getSize();
                        if ($imageSize->getWidth() > $this->maxWidth) {
                            $imageSize = $imageSize->widen($this->maxWidth);
                        }
                        if ($imageSize->getHeight() > $this->maxHeight) {
                            $imageSize = $imageSize->heighten($this->maxHeight);
                        }
                        if ($imageSize !== $originalSize) {
                            $image->resize($imageSize);
                        }
                        $image->save(null, ['quality' => $this->quality]);
                    } catch (\Exception $e) {
                        Yii::error($e->getMessage());
                    }

                    if ($path) {
                        $output[$this->responsePathParam] = $path;
                        $output[$this->responseUrlParam] = $this->getFileStorage()->baseUrl . '/' . $path;
                        $output[$this->responseDeleteUrlParam] = Url::to([$this->deleteRoute, 'path' => $path]);
                        $paths = $this->session->get($this->sessionKey, []);
                        $paths[] = $path;
                        $this->session->set($this->sessionKey, $paths);
                        $this->afterSave($path);
                    } else {
                        Yii::warning('Could not save uploaded photo');
                        $output['error'] = true;
                        $output['errors'] = [];
                    }
                } else {
                    $output['error'] = true;
                    $output['errors'] = $validationModel->errors;
                }
                $this->trigger(self::EVENT_AFTER_VALIDATION);
            } else {
                $output['error'] = true;
                $output['errors'] = $this->resolveErrorMessage($uploadedFile->error);
            }

            $result['files'][] = $output;
        }

        return $this->multiple ? $result : array_shift($result);
    }

    /**
     * @return \app\files\Storage|\trntv\filekit\Storage
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFileStorage()
    {
        return parent::getFileStorage();
    }
}
