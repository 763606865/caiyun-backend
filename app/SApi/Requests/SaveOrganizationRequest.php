<?php

namespace App\SApi\Requests;

use App\Models\PlatformOrganization;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** 组织新增及部分更新请求。 */
class SaveOrganizationRequest extends Request
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = $this->route('id');
        $rules = [
            'enterprise_id' => ['required', 'integer', 'exists:platform_enterprises,id'],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:platform_organizations,id'],
            'is_enabled' => ['sometimes', 'boolean'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'primary_type' => ['nullable', 'string', 'max:255'],
            'area_code' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'verified_status' => ['nullable', 'string', 'max:255'],
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
            Rule::unique('platform_organizations', 'code')->ignore($id),
        ];

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'enterprise_id' => '所属企业',
            'code' => '编码',
            'name' => '名称',
            'parent_id' => '上级组织',
            'is_enabled' => '启用状态',
            'short_name' => '简称',
            'primary_type' => '主要类型',
            'area_code' => '地区码',
            'address' => '地址',
            'logo' => 'Logo',
            'website' => '官方网站',
            'contact_name' => '联系人',
            'contact_mobile' => '联系电话',
            'contact_email' => '联系邮箱',
            'description' => '简介',
            'verified_status' => '认证状态',
            'remark' => '备注',
        ];
    }

    /** 通过基础字段校验后，再检查组织层级是否形成循环。 */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->route('id') || ! $this->filled('parent_id')) {
                return;
            }

            $parent = PlatformOrganization::query()->find($this->integer('parent_id'));
            $seen = [];

            while ($parent) {
                if ($parent->id === (int) $this->route('id') || isset($seen[$parent->id])) {
                    $validator->errors()->add('parent_id', '上级组织不能形成循环');

                    return;
                }

                $seen[$parent->id] = true;
                $parent = $parent->parent;
            }
        }];
    }
}
