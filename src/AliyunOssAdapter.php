<?php

namespace Fuguevit\Storage;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use OSS\Core\OssException;
use OSS\OssClient;

class AliyunOssAdapter extends AbstractAdapter
{
    /**
     * @var array
     */
    protected static $resultMap = [
        'Body'           => 'raw_contents',
        'Content-Length' => 'size',
        'ContentType'    => 'mimetype',
        'Size'           => 'size',
        'StorageClass'   => 'storage_class',
    ];

    /**
     * @var array
     */
    protected static $metaOptions = [
        'CacheControl',
        'Expires',
        'ServerSideEncryption',
        'Metadata',
        'ACL',
        'ContentType',
        'ContentDisposition',
        'ContentLanguage',
        'ContentEncoding',
    ];

    /**
     * @var array
     */
    protected static $metaMap = [
        'CacheControl'         => 'Cache-Control',
        'Expires'              => 'Expires',
        'ServerSideEncryption' => 'x-oss-server-side-encryption',
        'Metadata'             => 'x-oss-metadata-directive',
        'ACL'                  => 'x-oss-object-acl',
        'ContentType'          => 'Content-Type',
        'ContentDisposition'   => 'Content-Disposition',
        'ContentLanguage'      => 'response-content-language',
        'ContentEncoding'      => 'Content-Encoding',
    ];

    protected $client;

    protected $bucket;

    protected $options = [
        'Multipart' => 128,
    ];

    /**
     * AliyunOssAdapter constructor.
     *
     * @param OssClient $client
     * @param $bucket
     * @param null  $prefix
     * @param array $options
     */
    public function __construct(OssClient $client, $bucket, $prefix = null, $options = [])
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->setPathPrefix($prefix);
        $this->options = array_merge($this->options, $options);
    }

    public function getBucket()
    {
        return $this->bucket;
    }

    public function getClient()
    {
        return $this->getClient();
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
        $options = $this->getOptions($this->options, $config);

        if (!isset($options[OssClient::OSS_LENGTH])) {
            $options[OssClient::OSS_LENGTH] = Util::contentSize($contents);
        }

        if (!isset($options[OssClient::OSS_CONTENT_TYPE])) {
            $options[OssClient::OSS_CONTENT_TYPE] = Util::guessMimeType($path, $contents);
        }

        try {
            $this->client->putObject($this->getBucket(), $object, $contents, $options);
        } catch (OssException $e) {
            return false;
        }

        return $this->normalizeResponse($options, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    /**
     * Write file from its original path to destination.
     *
     * @param $path
     * @param $filePath
     * @param Config $config
     *
     * @return array|bool
     */
    public function writeFile($path, $filePath, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        $options = $this->getOptions($this->options, $config);

        $options[OssClient::OSS_CHECK_MD5] = true;

        if (!isset($options[OssClient::OSS_CONTENT_TYPE])) {
            $options[OssClient::OSS_CONTENT_TYPE] = Util::guessMimeType($path, '');
        }
        try {
            $this->client->uploadFile($this->bucket, $object, $filePath, $options);
        } catch (OssException $e) {
            return false;
        }

        return $this->normalizeResponse($options, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        if (! $config->has('visibility') && ! $config->has('ACL')) {
            $config->set(static::$metaMap['ACL'], $this->getObjectACL($path));
        }
        
        return $this->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);
        
        return $this->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        if (! $this->copy($path, $newpath)) {
            return false;
        }
        
        return $this->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $object = $this->applyPathPrefix($path);
        $newObject = $this->applyPathPrefix($newpath);
        
        try {
            $this->client->copyObject($this->bucket, $object, $this->bucket, $newObject);
        } catch (OssException $e) {
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
        
        try {
            $this->client->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            return false;    
        }
        
        return ! $this->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $dirname = rtrim($this->applyPathPrefix($dirname), '/').'/';
        $dirObjects = $this->listDirObjects($dirname, true);
        // when objects > 0, delete them all 
        if (count($dirObjects['objects'])) {
            foreach ($dirObjects['objects'] as $object) {
                $objects[] = $object['Key'];
            }
            
            try {
                $this->client->deleteObject($this->bucket, $objects);
            } catch (OssException $e) {
                return false;
            }
        }
        
        // delete directory
        try {
            $this->client->deleteObject($this->bucket, $dirname);
        } catch (OssException $e) {
            return false;
        }
        
        return true;
    }

    /**
     * List objects under specified directory, can be recursive.
     *
     * @param string $dirname
     * @param bool $recursive
     * 
     * @return mixed
     */
    public function listDirObjects($dirname = '', $recursive = false)
    {
        $result = [];
        
        $delimiter = '/';
        $nextMarker = '';
        $maxkeys = 1000;
        
        $options = array(
            'delimiter' => $delimiter,
            'prefix'    => $dirname,
            'max-keys'  => $maxkeys,
            'marker'    => $nextMarker
        );
        
        try {
            $listObjectInfo = $this->client->listObjects($this->bucket, $options);
        } catch (OssException $e) {
            return false;
        }
        
        $objectList = $listObjectInfo->getObjectList();
        $prefixList = $listObjectInfo->getPrefixList();
        
        if (! empty($objectList)) {
            foreach ($objectList as $objectInfo) {
                $object['Prefix']       = $dirname;
                $object['Key']          = $objectInfo->getKey();
                $object['LastModified'] = $objectInfo->getLastModified();
                $object['eTag']         = $objectInfo->getETag();
                $object['Type']         = $objectInfo->getType();
                $object['Size']         = $objectInfo->getSize();
                $object['StorageClass'] = $objectInfo->getStorageClass();
                
                $result['objects'][] = $object;
            }
        } else {
            $result['objects'] = [];
        }
        
        if (! empty($prefixList)) {
            foreach ($prefixList as $prefixInfo) {
                $result['prefix'][] = $prefixInfo->getPrefix();
            }
        } else {
            $result['prefix'] = [];
        }
        
        if ($recursive) {
            foreach ($result['prefix'] as $prefix) {
                $next = $this->listDirObjects($prefix, $recursive);
                $result['objects'] = array_merge($result['objects'], $next['objects']);
            }
        }
        
        return $result;
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

    /**
     * Get options.
     *
     * @param array       $options
     * @param Config|null $config
     *
     * @return array
     */
    protected function getOptions($options = [], Config $config = null)
    {
        $options = array_merge($this->options, $options);
        if ($config) {
            $options = array_merge($options, $this->getOptionsFromConfig($config));
        }

        return [OssClient::OSS_HEADERS => $options];
    }

    /**
     * Get options from configuration file.
     *
     * @param Config $config
     *
     * @return array
     */
    protected function getOptionsFromConfig(Config $config)
    {
        $options = [];

        foreach (static::$metaOptions as $option) {
            if (!$config->has($option)) {
                continue;
            }
            $options[static::$metaMap[$option]] = $config->get($option);
        }

        if ($visibility = $config->get('visibility')) {
            $options['x-oss-object-acl'] = $visibility === AdapterInterface::VISIBILITY_PUBLIC ? OssClient::OSS_ACL_TYPE_PUBLIC_READ : OssClient::OSS_ACL_TYPE_PRIVATE;
        }

        if ($mimetype = $config->get('mimetype')) {
            $options['Content-Type'] = $mimetype;
        }

        return $options;
    }

    /**
     * The ACL visibility.
     * 
     * @param $path
     * @return string
     */
    protected function getObjectACL($path)
    {
        $metadata = $this->getVisibility($path);
        
        return $metadata['visibility'] === AdapterInterface::VISIBILITY_PUBLIC ? OssClient::OSS_ACL_TYPE_PUBLIC_READ : OssClient::OSS_ACL_TYPE_PRIVATE;
    }

    /**
     * Normalize Response.
     *
     * @param array $object
     * @param null  $path
     *
     * @return array
     */
    protected function normalizeResponse(array $object, $path = null)
    {
        $result = ['path' => $path ?: $this->removePathPrefix(isset($object['Key']) ? $object['Key'] : $object['Prefix'])];
        $result['dirname'] = Util::dirname($result['path']);

        if (isset($object['LastModified'])) {
            $result['timestamp'] = strtotime($object['LastModified']);
        }

        if (substr($result['pah'], -1) === '/') {
            $result['type'] = 'dir';
            $result['path'] = rtrim($result['path'], '/');

            return $result;
        }

        $result = array_merge($result, Util::map($object, static::$resultMap), ['type' => 'file']);

        return $result;
    }
}
