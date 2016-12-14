<?php

namespace Fuguevit\Storage\Tests;

use Dotenv\Dotenv;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected $ossConfList;
    
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initDotEnv();
        $this->initOssConfig();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        
        $app['path.base'] = __DIR__ . '/../src';
        $app['config']->set('filesystems.default', 'oss');
        $app['config']->set('filesystems.disks.oss', $this->ossConfList);
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
            'debug'       => env('OSS_DEBUG', '')
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
