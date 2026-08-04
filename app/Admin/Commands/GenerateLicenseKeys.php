<?php

namespace App\Admin\Commands;

use Illuminate\Console\Command;
use OpenSSLAsymmetricKey;

class GenerateLicenseKeys extends Command
{
    protected $signature = 'license:keys {--force : 覆盖已有密钥}';

    protected $description = '生成授权服务使用的 RSA 私钥和客户端验签公钥';

    public function handle(): int
    {
        $privatePath = (string) config('licensing.private_key_path');
        $publicPath = (string) config('licensing.public_key_path');
        if (! $this->option('force') && (is_file($privatePath) || is_file($publicPath))) {
            $this->error('密钥文件已存在；如确需轮换，请使用 --force。轮换会使旧授权包失效。');

            return self::FAILURE;
        }

        $key = openssl_pkey_new(['private_key_bits' => 3072, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if (! $key instanceof OpenSSLAsymmetricKey || ! openssl_pkey_export($key, $privatePem)) {
            $this->error('RSA 私钥生成失败。');

            return self::FAILURE;
        }
        $details = openssl_pkey_get_details($key);
        if (! is_array($details) || ! isset($details['key'])) {
            $this->error('RSA 公钥导出失败。');

            return self::FAILURE;
        }

        foreach ([$privatePath, $publicPath] as $path) {
            if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0750, true) && ! is_dir(dirname($path))) {
                $this->error("无法创建密钥目录：{$path}");

                return self::FAILURE;
            }
        }
        if (file_put_contents($privatePath, $privatePem, LOCK_EX) === false || file_put_contents($publicPath, $details['key'], LOCK_EX) === false) {
            $this->error('密钥文件写入失败。');

            return self::FAILURE;
        }
        chmod($privatePath, 0600);
        chmod($publicPath, 0644);

        $this->info("私钥：{$privatePath}");
        $this->info("客户端公钥：{$publicPath}");
        $this->warn('请安全备份私钥，只将 public.pem 分发给客户端项目。');

        return self::SUCCESS;
    }
}
