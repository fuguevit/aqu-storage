<?php

namespace Fuguevit\Storage\Tests;

use Dotenv\Dotenv;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected $ossConfList;
    protected $qiniuConfList;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initDotEnv();
        $this->initOssConfig();
        $this->initQiniuConfig();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['path.base'] = __DIR__.'/../src';
        $app['config']->set('filesystems.default', 'oss');
        $app['config']->set('filesystems.disks.oss', $this->ossConfList);
        $app['config']->set('filesystems.disks.qiniu', $this->qiniuConfList);
    }

    protected function initDotEnv()
    {
        $dotenv = new Dotenv(__DIR__.'/../');
        $dotenv->overload();
    }

    /**
     * Init Oss Configuration.
     */
    protected function initOssConfig()
    {
        $this->ossConfList = [
            'driver'      => 'oss',
            'access_id'   => env('OSS_ACCESS_ID', ''),
            'access_key'  => env('OSS_ACCESS_KEY', ''),
            'bucket'      => env('OSS_BUCKET', ''),
            'endpoint'    => env('OSS_ENDPOINT', ''),
            'isCName'     => env('OSS_IS_CNAME', ''),
            'debug'       => env('OSS_DEBUG', ''),
        ];
    }

    /**
     * Init Qiniu Configuration.
     */
    protected function initQiniuConfig()
    {
        $this->qiniuConfList = [
            'driver'      => 'qiniu',
            'access_key'  => env('QINIU_ACCESS_KEY', ''),
            'secret_key'  => env('QINIU_SECRET_KEY', ''),
            'bucket'      => env('QINIU_BUCKET'),
            'base_url'    => env('QINIU_BASEURL'),
            'debug'       => env('QINIU_DEBUG'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            'Fuguevit\Storage\Providers\AquStorageServiceProvider',
        ];
    }
}
