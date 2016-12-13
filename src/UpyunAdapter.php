<?php

namespace Fuguevit\Storage;

use League\Flysystem\Config;
use League\Flysystem\Adapter\AbstractAdapter;

class UpyunAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {

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

    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {

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