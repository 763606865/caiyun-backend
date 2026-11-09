<?php

namespace App\SApi\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** SAPI 公共请求校验；应用身份由路由鉴权中间件校验。 */
abstract class Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute不能为空',
            'string' => ':attribute必须是字符串',
            'integer' => ':attribute必须是整数',
            'boolean' => ':attribute必须是布尔值',
            'email' => ':attribute格式不正确',
            'exists' => ':attribute不存在',
            'unique' => ':attribute已存在',
            'in' => ':attribute不在允许范围内',
            'min' => ':attribute不能少于:min个字符',
            'max' => ':attribute不能超过:max个字符',
            'per_page.min' => ':attribute不能小于:min',
            'per_page.max' => ':attribute不能大于:max',
        ];
    }
}
