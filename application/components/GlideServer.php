<?php

namespace app\components;

use InvalidArgumentException;
use League\Flysystem\FileExistsException;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Filesystem\FilesystemException;
use League\Glide\Server;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components
 */
class GlideServer extends Server
{
    /**
     * @var string
     */
    protected $tempDir;

    /**
     * Generate manipulated image.
     *
     * @param string $path Image path.
     * @param array $params Image manipulation params.
     * @return string                Cache path.
     * @throws FileNotFoundException
     * @throws FilesystemException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function makeImage($path, array $params)
    {
        $sourcePath = $this->getSourcePath($path);
        $cachedPath = $this->getCachePath($path, $params);

        if ($this->cacheFileExists($path, $params) === true) {
            return $cachedPath;
        }

        if ($this->sourceFileExists($path) === false) {
            throw new FileNotFoundException(
                'Could not find the image `'.$sourcePath.'`.'
            );
        }

        $source = $this->source->read(
            $sourcePath
        );

        if ($source === false) {
            throw new FilesystemException(
                'Could not read the image `'.$sourcePath.'`.'
            );
        }

        // We need to write the image to the local disk before
        // doing any manipulations. This is because EXIF data
        // can only be read from an actual file.
        if (isset($this->tempDir)) {
            $tempDir = $this->tempDir;
        } else {
            $tempDir = !empty(ini_get('upload_tmp_dir')) ?
                ini_get('upload_tmp_dir') : sys_get_temp_dir();
        }
        $tmp = tempnam($tempDir, 'Glide');

        if (file_put_contents($tmp, $source) === false) {
            throw new FilesystemException(
                'Unable to write temp file for `'.$sourcePath.'`.'
            );
        }

        try {
            $write = $this->cache->write(
                $cachedPath,
                $this->api->run($tmp, $this->getAllParams($params))
            );

            if ($write === false) {
                throw new FilesystemException(
                    'Could not write the image `'.$cachedPath.'`.'
                );
            }
        } catch (FileExistsException $exception) {
            // This edge case occurs when the target already exists
            // because it's currently be written to disk in another
            // request. It's best to just fail silently.
        } finally {
            unlink($tmp);
        }

        return $cachedPath;
    }

    /**
     * @param $path
     */
    public function setTempDir($path)
    {
        if (is_dir($path) && is_writable($path)) {
            $this->tempDir = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            throw new InvalidArgumentException(
                sprintf('%s is not a local directory or not writable', $path)
            );
        }
    }

    /**
     * Generate and output image.
     * @param string $path
     * @param array $params
     * @return bool|string|void
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \League\Glide\Filesystem\FileNotFoundException
     * @throws \League\Glide\Filesystem\FilesystemException
     */
    public function outputImage($path, array $params)
    {
        $disableFunctions = explode(',', ini_get('disable_functions'));

        $path = $this->makeImage($path, $params);

        header('Content-Type:'.$this->cache->getMimetype($path));
        header('Content-Length:'.$this->cache->getSize($path));
        header('Cache-Control:'.'max-age=31536000, public');
        header('Expires:'.date_create('+1 years')->format('D, d M Y H:i:s').' GMT');

        $stream = $this->cache->readStream($path);

        if (ftell($stream) !== 0) {
            rewind($stream);
        }

        if (!in_array('fpassthru', $disableFunctions)) {
            fpassthru($stream);
        } elseif (!in_array('stream_get_contents', $disableFunctions)) {
            echo stream_get_contents($stream);
        } else {
            Yii::error('Could not output image because of PHP configuration, see php.ini "disable_functions"');
        }

        fclose($stream);
    }
}
