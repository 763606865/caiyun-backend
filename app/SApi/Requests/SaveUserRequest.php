<?php

namespace App\SApi\Requests;

use Illuminate\Validation\Rule;

/** 用户新增及部分更新请求。 */
class SaveUserRequest extends Request
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = $this->route('id');
        $rules = [
            'real_name' => ['nullable', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ];

        // 更新时只校验提交的字段，保留创建时的必填约束。
        if ($id !== null) {
            foreach ($rules as &$rule) {
                $rule = ['sometimes', ...$rule];
            }
            unset($rule);
        }

        $rules['phone'] = [
            ...$rules['phone'],
            Rule::unique('users', 'phone')->ignore($id),
        ];

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'real_name' => '真实姓名',
            'nick_name' => '昵称',
            'phone' => '手机号',
            'email' => '邮箱',
            'status' => '用户状态',
        ];
    }
}
