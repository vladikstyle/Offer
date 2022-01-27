<?php

namespace app\actions;

use app\traits\RequestResponseTrait;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\base\NotSupportedException;
use yii\web\Response;
use trntv\glide\components\Glide;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\actions
 */
class GlideAction extends \yii\base\Action
{
    use RequestResponseTrait;

    /**
     * @var string|callable
     */
    public $imageFile;
    /**
     * @var string
     */
    public $glideComponent = 'glide';

    /**
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws NotSupportedException
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        /** @var Glide $glide */
        $glide = Yii::$app->get($this->glideComponent);
        $server = $glide->getServer();
        $image = $this->imageFile;

        if (is_callable($this->imageFile)) {
            $image = call_user_func($this->imageFile);
        }

        if (!$server->sourceFileExists($image)) {
            throw new NotFoundHttpException('Image not found.');
        }

        if ($glide->signKey) {
            $request = Request::create($this->request->getUrl());
            if (!$glide->validateRequest($request)) {
                throw new BadRequestHttpException('Wrong signature');
            }
        }

        try {
            $this->response->format = Response::FORMAT_RAW;
            $server->outputImage($image, $this->request->get());
            exit;
        } catch (\Exception $e) {
            throw new NotSupportedException($e->getMessage());
        }
    }
}
