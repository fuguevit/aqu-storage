<?php

namespace Fuguevit\Storage\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AliyunOssTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test method write.
     *
     * @return mixed
     */
    public function test_it_can_push_file_to_oss()
    {
        Storage::put('samples/sample_img.jpg', new File(__DIR__.'/assets/sample_img.jpg'));

        $this->assertTrue(true);
    }
}
