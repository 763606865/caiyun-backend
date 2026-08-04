<?php

namespace App\Api\Controllers;

use App\Services\OssService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    /**
     * 上传单个文件至 OSS。
     *
     * POST /api/files/upload
     */
    public function upload(Request $request, OssService $oss): JsonResponse
    {
        /** @var array{file: UploadedFile, directory?: string} $validated */
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.config('upload.max_size'),
                'extensions:'.implode(',', config('upload.allowed_extensions')),
            ],
            'directory' => [
                'sometimes',
                'string',
                Rule::in(config('upload.directories')),
            ],
        ]);

        return $this->success([
            'message' => '文件上传成功',
            'file' => $oss->upload(
                $validated['file'],
                $validated['directory'] ?? 'other',
            ),
        ]);
    }
}
