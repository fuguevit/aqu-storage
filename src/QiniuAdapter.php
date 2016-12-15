<?php

namespace Fuguevit\Storage;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class QiniuAdapter extends AbstractAdapter
{
    /**
     * @var Auth
     */
    protected $auth;
    
    protected $bucket;
    
    public function __construct(Auth $auth, $bucket)
    {
        $this->auth = $auth;
        $this->bucket = $bucket;
    }

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
     * @param $path
     * @param $filePath
     * @param Config $config
     * @return array|bool
     */
    public function writeFile($path , $filePath, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        $token = $this->auth->uploadToken($this->bucket);
        $uploadMgr = new UploadManager();
        try {
           $result = $uploadMgr->putFile($token, $object, $filePath);
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
