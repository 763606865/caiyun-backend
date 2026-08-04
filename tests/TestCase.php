<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'X-Client-Type' => 'web',
            'X-App-Version' => '1.0.0',
            'X-App-Build' => '10000',
            'X-Platform' => 'web',
            'X-OS-Version' => 'test',
            'X-Device-ID' => 'phpunit-device',
            'X-Channel' => 'test',
        ]);
    }
}
