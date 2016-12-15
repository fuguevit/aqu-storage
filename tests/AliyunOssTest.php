<?php

namespace Fuguevit\Storage\Tests;

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
     * Test method put.
     *
     * @return mixed
     */
    public function test_it_can_push_file_to_oss()
    {
        Storage::put('samples/sample_img.jpg', file_get_contents(__DIR__.'/assets/sample_img.jpg'));

        $this->assertTrue(Storage::exists('samples/sample_img.jpg'));
    }

    /**
     * Test method delete.
     */
    public function test_it_can_delete_file_from_oss()
    {
        Storage::delete('samples/sample_img.jpg');

        $this->assertTrue(!Storage::exists('samples/sample_img.jpg'));
    }

    /**
     * Test method putFile.
     */
    public function test_it_can_put_with_file_path_to_oss()
    {
        Storage::putFile('samples/sample_img.jpg', __DIR__.'/assets/sample_img.jpg');

        $this->assertTrue(Storage::exists('samples/sample_img.jpg'));
    }
}
