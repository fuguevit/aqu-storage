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

    /**
     * @var string
     */
    protected $bucket;

    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct(Auth $auth, $bucket, $baseUrl)
    {
        $this->auth = $auth;
        $this->bucket = $bucket;
        $this->baseUrl = $baseUrl;
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
            $err = $bucketMgr->delete($this->bucket, $object);
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
    public function deleteDir($dirname)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        return false;
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
        $url = $this->generatePrivateDownloadUrl($path, true);

        $authUrl = $this->auth->privateDownloadUrl($url);
        $result['contents'] = file_get_contents($authUrl);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $url = $this->generatePrivateDownloadUrl($path, true);
        if ($stream = fopen($url, 'r')) {
            $result['stream'] = $stream;

            return $result;
        }

        return false;
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
        return false;
    }

    /**
     * Generate private download url.
     *
     * @param $path
     * @param $needHeader
     *
     * @return string
     */
    protected function generatePrivateDownloadUrl($path, $needHeader = false)
    {
        $object = $this->applyPathPrefix($path);
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($object);
        if ($needHeader && !starts_with($url, 'http://')) {
            $url = 'http://'.$url;
        }

        return $url;
    }
}
