<?php

namespace Fuguevit\Storage\Tests;

use Illuminate\Support\Facades\Storage;

class UpyunTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * write method test.
     */
    public function test_it_can_write_contents()
    {
        Storage::disk('upyun')->put('samples/sample_img.jpg', file_get_contents(__DIR__.'/assets/sample_img.jpg'));
        $this->assertTrue(Storage::disk('upyun')->exists('samples/sample_img.jpg'));
    }

    /**
     * readStream test.
     */
    public function test_it_can_read_stream()
    {
        $result = Storage::disk('upyun')->readStream('samples/sample_img.jpg');
        $this->assertTrue(gettype($result) == 'resource');
    }

    /**
     * delete method test.
     */
    public function test_it_can_delete_file()
    {
        $this->assertTrue(Storage::disk('upyun')->delete('samples/sample_img.jpg'));
    }

    /**
     * putFile method test.
     */
    public function test_it_can_write_file()
    {
        Storage::disk('upyun')->putFile('samples/test_img.jpg', __DIR__.'/assets/sample_img.jpg');
        $this->assertTrue(Storage::disk('upyun')->exists('samples/test_img.jpg'));
    }

    /**
     * rename method test.
     */
    public function test_it_can_rename_file()
    {
        Storage::disk('upyun')->rename('samples/test_img.jpg', 'samples/test2_img.jpg');
        $this->assertTrue(Storage::disk('upyun')->exists('samples/test2_img.jpg'));
    }
}
