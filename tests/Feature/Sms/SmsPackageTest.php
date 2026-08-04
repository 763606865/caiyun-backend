<?php

namespace Tests\Feature\Sms;

use Caiyun\Sms\Contracts\SmsSender;
use Caiyun\Sms\SmsManager;
use Caiyun\Sms\SmsResult;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsPackageTest extends TestCase
{
    public function test_package_is_discovered_and_contract_is_bound(): void
    {
        $this->assertInstanceOf(SmsManager::class, app(SmsSender::class));
    }

    public function test_log_driver_sends_a_template_message(): void
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('SMS message', [
                'mobile' => '13800138000',
                'template' => 'verification_code',
                'parameters' => ['code' => '123456'],
            ]);

        $result = app(SmsSender::class)->send(
            '13800138000',
            'verification_code',
            ['code' => '123456'],
        );

        $this->assertInstanceOf(SmsResult::class, $result);
        $this->assertSame('log', $result->driver);
    }
}
