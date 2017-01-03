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
}
