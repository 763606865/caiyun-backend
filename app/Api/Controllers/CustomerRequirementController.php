<?php

namespace App\Api\Controllers;

use App\Enums\RequirementStatus;
use App\Enums\RequirementType;
use App\Models\CustomerRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * 客户需求公开提交接口。
 *
 * 接收官网访客提交的联系信息和项目需求。跟进状态、负责人及内部备注等字段
 * 只能由 Admin 后台维护，不能通过公开接口指定。
 */
class CustomerRequirementController extends Controller
{
    /**
     * 提交一条客户需求。
     *
     * POST /api/customer-requirements
     *
     * @param  Request  $request  包含联系人、联系方式、组织名称、需求类型和项目需求
     * @return JsonResponse HTTP 201，data 中包含需求 ID 和提交成功提示
     *
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        /** @var array{contact_name?: string|null, contact_method: string, organization?: string|null, requirement_type: string, requirements?: string|null} $data */
        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:100'],
            'contact_method' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'requirement_type' => ['required', Rule::enum(RequirementType::class)],
            'requirements' => ['nullable', 'string', 'min:10', 'max:10000'],
        ]);

        $requirement = CustomerRequirement::query()->create([
            ...$data,
            'status' => RequirementStatus::Pending,
            'source_ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        return $this->success([
            'id' => $requirement->id,
            'message' => '需求已提交，我们会尽快与您联系。',
        ], 201);
    }
}
