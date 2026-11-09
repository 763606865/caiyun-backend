<?php

namespace App\SApi\Requests;

use Illuminate\Validation\Rule;

/** 企业职工创建与部分更新校验。 */
class SaveEmployeeRequest extends Request
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $enterprise = $this->route('enterprise');
        $id = $this->route('id');
        $rules = [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at'), Rule::unique('platform_employees', 'user_id')->where('enterprise_id', $enterprise)->ignore($id)],
            'employee_no' => ['nullable', 'string', 'max:255', Rule::unique('platform_employees', 'employee_no')->where('enterprise_id', $enterprise)->ignore($id)],
            'position' => ['nullable', 'string', 'max:255'],
            'is_enabled' => ['sometimes', 'boolean'],
        ];
        if ($id !== null) {
            foreach ($rules as &$rule) {
                $rule = ['sometimes', ...$rule];
            } unset($rule);
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['user_id' => '职工用户', 'employee_no' => '职工编号', 'position' => '职务', 'is_enabled' => '在职启用状态'];
    }
}
