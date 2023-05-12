<?php

namespace TromsFylkestrafikk\RagnarokSink\Tests\Feature;

use TromsFylkestrafikk\RagnarokSink\Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
