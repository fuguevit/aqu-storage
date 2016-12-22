<?php

namespace Fuguevit\Storage\Tests;

use Illuminate\Support\Facades\Storage;

class QiniuTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test method put.
     */
    public function test_it_can_write_content_to_qiniu()
    {
        Storage::disk('qiniu')->put('samples/sample_img.png', file_get_contents(__DIR__.'/assets/sample_img.jpg'));
        $this->assertTrue(Storage::disk('qiniu')->exists('samples/sample_img.jpg'));
    }

    /**
     * Test method delete.
     */
    public function test_it_can_delete_object_from_qiniu()
    {
        Storage::disk('qiniu')->delete('samples/sample_img.jpg');
        $this->assertTrue(!Storage::disk('qiniu')->exists('samples/sample_img.jpg'));
    }

    /**
     * Test method putFile.
     */
    public function test_it_can_put_with_file_path_to_qiniu()
    {
        Storage::disk('qiniu')->putFile('samples/sample_img.jpg', __DIR__.'/assets/sample_img.jpg');
        $this->assertTrue(Storage::disk('qiniu')->exists('samples/sample_img.jpg'));
    }

    /**
     * Test method getMetadata.
     */
    public function test_it_can_get_metadata()
    {
        $result = Storage::disk('qiniu')->getMetadata('samples/sample_img.jpg');
        $this->assertArrayHasKey('fsize', $result);
    }

    /**
     * Test method read.
     */
    public function test_it_can_read_object()
    {
        $result = Storage::disk('qiniu')->read('samples/sample_img.jpg');
        $content = file_get_contents(__DIR__.'/assets/sample_img.jpg');
        $this->assertSame($result, $content);
    }

    /**
     * Test method readStream.
     */
    public function test_it_can_read_stream()
    {
        $result = Storage::disk('qiniu')->readStream('samples/sample_img.jpg');
        $this->assertTrue(gettype($result) == 'resource');
    }
}
