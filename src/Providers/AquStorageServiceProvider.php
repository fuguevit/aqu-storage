<?php

namespace Fuguevit\Storage\Providers;

use Fuguevit\Storage\AliyunOssAdapter;
use Fuguevit\Storage\Plugins\PutFile;
use Fuguevit\Storage\QiniuAdapter;
use Fuguevit\Storage\UpyunAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use OSS\OssClient;
use Qiniu\Auth;
use Upyun\Config;

class AquStorageServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->initAliyunOssAdapter();

        $this->initQiniuAdapter();

        $this->initUpyunAdapter();
    }

    /**
     * init AliyunOss adapter.
     */
    protected function initAliyunOssAdapter()
    {
        Storage::extend('oss', function ($app, $config) {
            $accessId = $config['access_id'];
            $accessKey = $config['access_key'];
            $endPoint = $config['endpoint'];
            $bucket = $config['bucket'];
            $isCName = $config['isCName'];
            $debug = $config['debug'];

            $client = new OssClient($accessId, $accessKey, $endPoint, $isCName);
            $adapter = new AliyunOssAdapter($client, $bucket);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new PutFile());

            return $filesystem;
        });
    }

    /**
     * init qiniu adapter.
     */
    protected function initQiniuAdapter()
    {
        Storage::extend('qiniu', function ($app, $config) {
            $accessKey = $config['access_key'];
            $secretKey = $config['secret_key'];
            $bucket = $config['bucket'];
            $baseUrl = $config['base_url'];
            $auth = new Auth($accessKey, $secretKey);
            $adapter = new QiniuAdapter($auth, $bucket, $baseUrl);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new PutFile());

            return $filesystem;
        });
    }

    /**
     * init Upyun adapter.
     */
    protected function initUpyunAdapter()
    {
        Storage::extend('upyun', function ($app, $config) {
            $bucket = $config['bucket'];
            $operatorName = $config['operator_name'];
            $operatorPwd = $config['operator_pwd'];
            $debug = $config['debug'];

            $bucketConfig = new Config($bucket, $operatorName, $operatorPwd);
            $adapter = new UpyunAdapter($bucketConfig);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new PutFile());

            return $filesystem;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
    }
}
