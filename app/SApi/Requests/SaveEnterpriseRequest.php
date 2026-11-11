<?php

namespace App\SApi\Requests;

use Illuminate\Validation\Rule;

/** 企业新增及部分更新请求。 */
class SaveEnterpriseRequest extends Request
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = $this->route('id');
        $rules = [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_enabled' => ['sometimes', 'boolean'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'credit_code' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:255'],
        ];

        // 更新时只校验提交的字段，保留创建时的必填约束。
        if ($id !== null) {
            foreach ($rules as &$rule) {
                $rule = ['sometimes', ...$rule];
            }
            unset($rule);
        }

        $rules['code'] = [
            ...$rules['code'],
            Rule::unique('platform_enterprises', 'code')->ignore($id),
        ];

        if ($id === null) {
            $rules['code'][] = Rule::unique('platform_organizations', 'code');
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'code' => '编码',
            'name' => '名称',
            'admin_user_id' => '企业管理员',
            'is_enabled' => '启用状态',
            'short_name' => '简称',
            'credit_code' => '统一社会信用代码',
            'contact_name' => '联系人',
            'contact_mobile' => '联系电话',
            'address' => '地址',
            'description' => '简介',
            'remark' => '备注',
        ];
    }
}
