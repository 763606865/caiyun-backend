<?php

namespace App\SApi\Requests;

use App\Models\PlatformApplication;
use App\Models\PlatformSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** 企业应用套餐创建与部分更新校验。 */
class SaveSubscriptionRequest extends Request
{
    /** 应用编码优先；在既有外键、唯一性校验前解析为内部应用 ID。 */
    protected function prepareForValidation(): void
    {
        if ($this->has('application_code')) {
            $code = $this->input('application_code');
            $this->merge([
                'application_id' => is_string($code)
                    ? PlatformApplication::query()->where('code', $code)->value('id')
                    : null,
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $enterprise = $this->route('enterprise');
        $id = $this->route('id');
        $rules = [
            'application_code' => ['sometimes', 'required', 'string', 'max:255', Rule::exists('platform_applications', 'code')],
            'application_id' => ['required', 'integer', Rule::exists('platform_applications', 'id'), Rule::unique('platform_subscriptions', 'application_id')->where('enterprise_id', $enterprise)->ignore($id)],
            'package_name' => ['required', 'string', 'max:255'],
            'seat_limit' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
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
    public function messages(): array
    {
        return [...parent::messages(), 'date' => ':attribute必须是有效日期', 'seat_limit.min' => '用户额度不能小于:min', 'seat_limit.max' => '用户额度不能大于:max'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['application_code' => '应用编码', 'application_id' => '应用', 'package_name' => '套餐名称', 'seat_limit' => '用户额度', 'starts_at' => '套餐开始时间', 'ends_at' => '套餐结束时间', 'is_enabled' => '启用状态'];
    }

    /** 合并更新前后的时间，避免部分更新产生无效套餐区间。 */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $record = $this->route('id') ? PlatformSubscription::query()->where('enterprise_id', $this->route('enterprise'))->find($this->route('id')) : null;
            $start = $this->input('starts_at', $record?->starts_at);
            $end = $this->input('ends_at', $record?->ends_at);
            if ($start && $end && Carbon::parse($end)->lessThanOrEqualTo(Carbon::parse($start))) {
                $validator->errors()->add('ends_at', '套餐结束时间必须晚于开始时间');
            }
        }];
    }
}
