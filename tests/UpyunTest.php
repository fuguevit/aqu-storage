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
     * putFile method test.
     */
    public function test_it_can_put_file()
    {
        Storage::disk('upyun')->putFile('samples/test_img.jpg', __DIR__.'/assets/sample_img.jpg');
        $this->assertTrue(Storage::disk('upyun')->exists('samples/test_img.jpg'));
    }

    /**
     * getSize method test.
     */
    public function test_it_can_get_size()
    {
        $size = Storage::disk('upyun')->getSize('samples/test_img.jpg');   
        $this->assertEquals(182871, $size);
    }

    /**
     * update method test.
     */
    public function test_it_can_update_file()
    {
        Storage::disk('upyun')->update('samples/test_img.jpg', file_get_contents(__DIR__.'/assets/sample2_img.jpg'));
        $this->assertTrue(Storage::disk('upyun')->exists('samples/test_img.jpg'));
    }
    
}
