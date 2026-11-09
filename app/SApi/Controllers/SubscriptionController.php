<?php

namespace App\SApi\Controllers;

use App\Models\PlatformEnterprise;
use App\SApi\Requests\PaginationRequest;
use App\SApi\Requests\SaveSubscriptionRequest;
use Illuminate\Http\JsonResponse;

/** 企业应用套餐接口，所有记录均按路径中的企业隔离。 */
class SubscriptionController extends Controller
{
    /**
     * GET /sapi/enterprises/{enterprise}/subscriptions
     *
     * @param  PaginationRequest  $request  per_page：1–100，默认 20；page：页码
     * @param  int  $enterprise  企业 ID
     * @return JsonResponse HTTP 200，data 为分页应用套餐列表
     */
    public function index(PaginationRequest $request, int $enterprise): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        $records = $company->subscriptions()
            ->with(['application:id,code,name'])
            ->orderByDesc('id')
            ->paginate($request->validated('per_page', 20));

        return $this->success($records);
    }

    /**
     * POST /sapi/enterprises/{enterprise}/subscriptions
     *
     * @param  SaveSubscriptionRequest  $request  JSON：application_id、package_name、seat_limit、starts_at、ends_at（均必填）、is_enabled（可选）
     * @param  int  $enterprise  企业 ID
     * @return JsonResponse HTTP 201，data 为新增应用套餐；校验失败返回 422
     */
    public function store(SaveSubscriptionRequest $request, int $enterprise): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        $record = $company->subscriptions()->create($request->validated());

        return $this->success($record->refresh()->load(['application:id,code,name']), 201);
    }

    /**
     * GET /sapi/enterprises/{enterprise}/subscriptions/{id}
     *
     * @param  int  $enterprise  企业 ID
     * @param  int  $id  应用套餐关联 ID
     * @return JsonResponse HTTP 200，data 为应用套餐详情；不存在或不属于本企业返回 404
     */
    public function show(int $enterprise, int $id): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        return $this->success($company->subscriptions()->with(['application:id,code,name'])->findOrFail($id));
    }

    /**
     * PUT/PATCH /sapi/enterprises/{enterprise}/subscriptions/{id}
     *
     * @param  SaveSubscriptionRequest  $request  与创建参数相同，仅更新提交字段
     * @param  int  $enterprise  企业 ID
     * @param  int  $id  应用套餐关联 ID
     * @return JsonResponse HTTP 200，data 为更新后的应用套餐；不存在返回 404，校验失败返回 422
     */
    public function update(SaveSubscriptionRequest $request, int $enterprise, int $id): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);

        $record = $company->subscriptions()->findOrFail($id);
        $record->update($request->validated());

        return $this->success($record->refresh()->load(['application:id,code,name']));
    }

    /**
     * DELETE /sapi/enterprises/{enterprise}/subscriptions/{id}
     *
     * @param  int  $enterprise  企业 ID
     * @param  int  $id  应用套餐关联 ID；无请求体
     * @return JsonResponse HTTP 200，data 为空数组；不存在返回 404
     */
    public function destroy(int $enterprise, int $id): JsonResponse
    {
        $company = PlatformEnterprise::query()->findOrFail($enterprise);
        $company->subscriptions()->findOrFail($id)->delete();

        return $this->success();
    }
}
