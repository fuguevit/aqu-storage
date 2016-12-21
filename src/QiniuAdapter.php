<?php

namespace Fuguevit\Storage;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
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
        if (gettype($contents) == 'resource') {
            $contents = stream_get_contents($contents);
        }

        $object = $this->applyPathPrefix($path);
        $token = $this->auth->uploadToken($this->bucket);
        // new upload manager
        $uploadMgr = new UploadManager();
        try {
            $result = $uploadMgr->put($token, $object, $contents);
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
        $token = $this->auth->uploadToken($this->bucket);
        // new upload manager
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
        return $this->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->write($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        $object = $this->applyPathPrefix($path);
        $newObject = $this->applyPathPrefix($newpath);
        // new bucket manager
        $bucketMgr = new BucketManager($this->auth);
        try {
            $result = $bucketMgr->move($this->bucket, $object, $this->bucket, $newObject);
        } catch (\Exception $e) {
            return false;
        }

        if ($result !== null) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $object = $this->applyPathPrefix($path);
        // new bucket manager
        $bucketMgr = new BucketManager($this->auth);
        try {
            $result = $bucketMgr->copy($this->bucket, $object, $this->bucket, $object);
        } catch (\Exception $e) {
            return false;
        }

        if ($result !== null) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $object = $this->applyPathPrefix($path);
        // new bucket manager
        $bucketMgr = new BucketManager($this->auth);
        try {
            $result = $bucketMgr->delete($this->bucket, $object);
        } catch (\Exception $e) {
            return false;
        }

        if ($result !== null) {
            return $result;
        }

        return true;
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
        // new bucket manager
        $bucketMgr = new BucketManager($this->auth);
        try {
            list($ret, $err) = $bucketMgr->stat($this->bucket, $object);
        } catch (\Exception $e) {
            return false;
        }

        if ($err !== null) {
            return false;
        }

        return true;
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
        $object = $this->applyPathPrefix($path);
        // new bucket manager
        $bucketMgr = new BucketManager($this->auth);
        try {
            list($ret, $err) = $bucketMgr->stat($this->bucket, $object);
        } catch (\Exception $e) {
            return false;
        }

        if ($err !== null) {
            return false;
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $object = $this->applyPathPrefix($path);
        $result = $this->getMetadata($object);
        if (!$result || !array_key_exists('fsize', $result)) {
            return false;
        }
        $result['size'] = $result['fsize'];
        
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        $object = $this->applyPathPrefix($path);
        $result = $this->getMetadata($object);
        if (!$result || !array_key_exists('mimeType', $result)) {
            return false;
        }
        $result['mimetype'] = $result['mimeType'];
        
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $object = $this->applyPathPrefix($path);
        $result = $this->getMetadata($object);
        if (!$result || !array_key_exists('putTime', $result)) {
            return false;
        }
        $result['timestamp'] = $result['putTime'];
        
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
    }
}
