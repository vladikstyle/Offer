<?php

namespace app\traits;

use Yii;
use yii\web\Request;
use yii\web\Response;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\traits
 * @property Request $request
 * @property Response $response
 */
trait RequestResponseTrait
{
    /**
     * @var string
     */
    protected $requestComponent = 'request';
    /**
     * @var Request
     */
    protected $requestComponentCached;
    /**
     * @var string
     */
    protected $responseComponent = 'response';
    /**
     * @var Response
     */
    protected $responseComponentCached;

    /**
     * @return Request|null|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getRequest()
    {
        if (!isset($this->requestComponentCached)) {
            $this->requestComponentCached = Yii::$app->get($this->requestComponent);
        }

        return $this->requestComponentCached;
    }

    /**
     * @return Response|null|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getResponse()
    {
        if (!isset($this->responseComponentCached)) {
            $this->responseComponentCached = Yii::$app->get($this->responseComponent);
        }

        return $this->responseComponentCached;
    }
}
