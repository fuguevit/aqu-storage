<?php

namespace Fuguevit\Storage\Providers;

use Fuguevit\Storage\AliyunOssAdapter;
use Fuguevit\Storage\Plugins\PutFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use OSS\OssClient;

class AquStorageServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
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
     * {@inheritdoc}
     */
    public function register()
    {
    }
}
