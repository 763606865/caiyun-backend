<?php

namespace App\SApi\Controllers;

use App\Models\PlatformEnterprise;
use App\SApi\Requests\PaginationRequest;
use App\SApi\Requests\SaveEmployeeRequest;
use Illuminate\Http\JsonResponse;

/** 企业职工接口，所有记录均按路径中的企业隔离。 */
class EmployeeController extends Controller
{
    /**
     * GET /sapi/enterprises/{enterprise}/employees
     *
     * @param  PaginationRequest  $request  per_page：1–100，默认 20；page：页码
     * @param  int  $enterprise  企业 ID
     * @return JsonResponse HTTP 200，data 为分页职工列表
     */
    public function index(PaginationRequest $request, int $enterprise): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        $records = $company->employees()
            ->with(['user'])
            ->orderByDesc('id')
            ->paginate($request->validated('per_page', 20));

        return $this->success($records);
    }

    /**
     * POST /sapi/enterprises/{enterprise}/employees
     *
     * @param  SaveEmployeeRequest  $request  JSON：user_id（必填）、employee_no、position、is_enabled
     * @param  int  $enterprise  企业 ID
     * @return JsonResponse HTTP 201，data 为新增职工；校验失败返回 422
     */
    public function store(SaveEmployeeRequest $request, int $enterprise): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        $record = $company->employees()->create($request->validated());

        return $this->success($record->refresh()->load(['user']), 201);
    }

    /**
     * GET /sapi/enterprises/{enterprise}/employees/{id}
     *
     * @param  int  $enterprise  企业 ID
     * @param  int  $id  职工关联 ID
     * @return JsonResponse HTTP 200，data 为职工详情；不存在或不属于本企业返回 404
     */
    public function show(int $enterprise, int $id): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        return $this->success($company->employees()->with(['user'])->findOrFail($id));
    }

    /**
     * PUT/PATCH /sapi/enterprises/{enterprise}/employees/{id}
     *
     * @param  SaveEmployeeRequest  $request  与创建参数相同，仅更新提交字段
     * @param  int  $enterprise  企业 ID
     * @param  int  $id  职工关联 ID
     * @return JsonResponse HTTP 200，data 为更新后的职工；不存在返回 404，校验失败返回 422
     */
    public function update(SaveEmployeeRequest $request, int $enterprise, int $id): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        $record = $company->employees()->findOrFail($id);
        $record->update($request->validated());

        return $this->success($record->refresh()->load(['user']));
    }

    /**
     * DELETE /sapi/enterprises/{enterprise}/employees/{id}
     *
     * @param  int  $enterprise  企业 ID
     * @param  int  $id  职工关联 ID；无请求体
     * @return JsonResponse HTTP 200，data 为空数组；不存在返回 404
     */
    public function destroy(int $enterprise, int $id): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);
        $company->employees()->findOrFail($id)->delete();

        return $this->success();
    }
}
