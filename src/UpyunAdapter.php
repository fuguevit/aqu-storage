<?php

namespace Fuguevit\Storage;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Upyun\Upyun;

class UpyunAdapter extends AbstractAdapter
{
    /**
     * @var
     */
    protected $config;

    /**
     * @var
     */
    protected $client;

    public function __construct($config)
    {
        $this->config = $config;
        $this->client = new Upyun($config);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        if (gettype($contents) == 'resource') {
            $contents = stream_get_contents($contents);
        }

        $object = $this->applyPathPrefix($path);
        try {
            $result = $this->client->write($object, $contents);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, $resource, $config);
    }

    /**
     * @param $path
     * @param $filePath
     * @param Config $config
     *
     * @return array|bool
     */
    public function writeFile($path, $filePath, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $result = $this->client->write($object, file_get_contents($filePath));
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $result = $this->client->has($object);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $result['contents'] = $this->client->read($object);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
    }
}
