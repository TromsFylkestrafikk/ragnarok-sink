<?php

namespace Ragnarok\Sink\Tests\Unit;

use Ragnarok\Sink\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    /** @test */
    public function basicTrueTest()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function basicFalseTest()
    {
        $this->assertNotTrue(false);
    }
}
