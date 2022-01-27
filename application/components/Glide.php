<?php

namespace app\components;

use app\helpers\Url;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components
 */
class Glide extends \trntv\glide\components\Glide
{
    /**
     * @var GlideServer
     */
    public $server;
    /**
     * @var string
     */
    public $cacheUrl;
    /**
     * @var string
     */
    public $tempDir = '@app/runtime/cache';

    public function init()
    {
        parent::init();
        if (isset($this->cacheUrl)) {
            $this->cacheUrl = Yii::getAlias($this->cacheUrl);
        }
    }

    /**
     * @return GlideServer|\League\Glide\Server
     */
    public function getServer()
    {
        if (!$this->server) {
            $server = new GlideServer($this->getSource(), $this->getCache(), $this->getApi());
            $server->setSourcePathPrefix($this->sourcePathPrefix);
            $server->setCachePathPrefix($this->cachePathPrefix);
            $server->setGroupCacheInFolders($this->groupCacheInFolders);
            $server->setDefaults($this->defaults);
            $server->setPresets($this->presets);
            $server->setBaseUrl($this->baseUrl);
            $server->setResponseFactory($this->responseFactory);
            $server->setCacheWithFileExtensions(true);
            $server->setTempDir(Yii::getAlias($this->tempDir));
            $this->server = $server;
        }

        return $this->server;
    }

    /**
     * @param $path
     * @param array $params
     * @return bool
     */
    public function cachedFileExists($path, $params = [])
    {
        $cachedPath = $this->getServer()->getCachePath($path, $params);

        return $this->getCache()->has($cachedPath);
    }

    /**
     * @param $path
     * @param array $params
     * @return string
     */
    public function getCachedImage($path, $params = [])
    {
        return Url::to(rtrim($this->cacheUrl, '/') . '/' . $this->getServer()->getCachePath($path, $params), true);
    }
}
