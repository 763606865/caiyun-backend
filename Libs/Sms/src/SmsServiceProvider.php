<?php

namespace Caiyun\Sms;

use Caiyun\Sms\Contracts\SmsSender;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sms.php', 'sms');

        $this->app->singleton(SmsManager::class);
        $this->app->alias(SmsManager::class, 'sms');
        $this->app->alias(SmsManager::class, SmsSender::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/sms.php' => config_path('sms.php'),
        ], 'sms-config');
    }
}
