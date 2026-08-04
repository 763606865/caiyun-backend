<?php

namespace Caiyun\Sms;

use Caiyun\Sms\Contracts\SmsSender;
use Caiyun\Sms\Drivers\AliyunDriver;
use Caiyun\Sms\Drivers\LogDriver;
use Caiyun\Sms\Drivers\TencentDriver;
use Caiyun\Sms\Exceptions\SmsException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Log\LogManager;
use Illuminate\Support\Manager;

class SmsManager extends Manager implements SmsSender
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('sms.default', 'log');
    }

    public function send(string $mobile, string $template, array $parameters = []): SmsResult
    {
        return $this->driver()->send(
            $mobile,
            $this->resolveTemplate($template),
            $parameters,
        );
    }

    protected function createLogDriver(): SmsSender
    {
        $config = $this->driverConfig('log');

        return new LogDriver(
            $this->container->make(LogManager::class),
            $config['channel'] ?? null,
        );
    }

    protected function createAliyunDriver(): SmsSender
    {
        return new AliyunDriver($this->driverConfig('aliyun'));
    }

    protected function createTencentDriver(): SmsSender
    {
        return new TencentDriver($this->driverConfig('tencent'));
    }

    private function resolveTemplate(string $template): string
    {
        $templateId = $this->config->get("sms.templates.{$template}", $template);

        if (! is_string($templateId) || $templateId === '') {
            throw new SmsException("SMS template [{$template}] is not configured.");
        }

        return $templateId;
    }

    /**
     * @return array<string, mixed>
     */
    private function driverConfig(string $driver): array
    {
        $config = $this->config->get("sms.drivers.{$driver}", []);

        return is_array($config) ? $config : [];
    }
}
