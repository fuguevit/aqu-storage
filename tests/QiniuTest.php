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
     * Test method putFile.
     */
    public function test_it_can_put_with_file_path_to_qiniu()
    {
        Storage::disk('qiniu')->putFile('samples/sample_img.jpg', __DIR__.'/assets/sample_img.jpg');

        $this->assertTrue(Storage::disk('qiniu')->exists('samples/sample_img.jpg'));
    }
}
