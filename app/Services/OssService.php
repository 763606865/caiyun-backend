<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OssService
{
    /**
     * 将文件上传至配置的 OSS 磁盘。
     *
     * 服务端生成不可预测的对象名，避免用户原始文件名造成覆盖或路径注入。
     *
     * @return array{
     *     path: string,
     *     url: string,
     *     original_name: string,
     *     mime_type: string,
     *     extension: string,
     *     size: int
     * }
     */
    public function upload(UploadedFile $file, string $directory = 'other'): array
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $path = sprintf(
            '%s/%s/%s.%s',
            trim($directory, '/'),
            now()->format('Y/m/d'),
            Str::uuid()->toString(),
            $extension,
        );
        $stream = fopen($file->getRealPath(), 'rb');

        if ($stream === false) {
            throw new RuntimeException('无法读取上传文件');
        }

        try {
            $disk = Storage::disk((string) config('upload.disk', 'oss'));

            if (! $disk->put($path, $stream)) {
                throw new RuntimeException('文件上传失败');
            }

            return [
                'path' => $path,
                'url' => $disk->url($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'extension' => $extension,
                'size' => $file->getSize(),
            ];
        } finally {
            fclose($stream);
        }
    }
}
