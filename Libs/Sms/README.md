# Caiyun SMS

适用于 Laravel 13+ 的可复制、多驱动短信组件，默认支持：

- `log`：本地开发和测试
- `aliyun`：阿里云短信
- `tencent`：腾讯云短信

## 在其他项目中安装

将整个 `Libs` 目录复制到 Laravel 项目根目录，然后执行：

```bash
composer config repositories.caiyun-sms path Libs/Sms
composer require caiyun/sms:@dev
php artisan vendor:publish --tag=sms-config
```

Laravel 会通过 Composer package discovery 自动注册服务提供者。

使用阿里云驱动时额外安装：

```bash
composer require alibabacloud/dysmsapi-20170525
```

使用腾讯云驱动时额外安装：

```bash
composer require tencentcloud/tencentcloud-sdk-php
```

## 配置

```env
SMS_DRIVER=log
SMS_VERIFICATION_CODE_TEMPLATE=verification_code

SMS_LOG_CHANNEL=

ALIYUN_SMS_ACCESS_KEY_ID=
ALIYUN_SMS_ACCESS_KEY_SECRET=
ALIYUN_SMS_SIGN_NAME=
ALIYUN_SMS_ENDPOINT=dysmsapi.aliyuncs.com

TENCENT_SMS_SECRET_ID=
TENCENT_SMS_SECRET_KEY=
TENCENT_SMS_SDK_APP_ID=
TENCENT_SMS_SIGN_NAME=
TENCENT_SMS_REGION=ap-guangzhou
```

业务代码使用模板别名，厂商模板 ID 只出现在配置中：

```php
use Caiyun\Sms\Contracts\SmsSender;

public function sendCode(SmsSender $sms): void
{
    $sms->send('13800138000', 'verification_code', [
        'code' => '123456',
    ]);
}
```

也可以使用 Facade：

```php
use Caiyun\Sms\Facades\Sms;

Sms::send('13800138000', 'verification_code', ['code' => '123456']);
```

临时指定驱动：

```php
Sms::driver('tencent')->send(
    '13800138000',
    config('sms.templates.verification_code'),
    ['123456'],
);
```

腾讯云模板参数按数组顺序传递，因此建议腾讯云调用时使用数字索引数组；阿里云模板参数使用关联数组。

## 自定义驱动

在应用服务提供者中扩展 Manager：

```php
use Caiyun\Sms\SmsManager;

app(SmsManager::class)->extend('custom', function ($app) {
    $config = $app['config']->get('sms.drivers.custom', []);

    return new CustomSmsDriver($config);
});
```

自定义驱动需要实现 `Caiyun\Sms\Contracts\SmsSender`。
