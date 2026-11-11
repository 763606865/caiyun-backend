<?php

namespace App\SApi\Controllers;

use App\Enums\OrganizationType;
use App\Models\PlatformEnterprise;
use App\Models\PlatformOrganization;
use App\SApi\Requests\PaginationRequest;
use App\SApi\Requests\SaveEnterpriseRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/** 中台企业管理接口。 */
class EnterpriseController extends Controller
{
    /**
     * 分页查询企业。
     *
     * GET /sapi/enterprises
     *
     * @param  PaginationRequest  $request  查询参数：per_page（可选，1–100，默认 20）、page（可选，默认 1）
     * @return JsonResponse HTTP 200，data 包含 data（企业列表）、current_page、per_page、total 等分页信息；外层包含 code、meta
     */
    public function index(PaginationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $records = PlatformEnterprise::query()
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 20);

        return $this->success($records);
    }

    /**
     * 查询企业详情。
     *
     * GET /sapi/enterprises/{id}
     *
     * @param  int  $id  路径参数，中台企业 ID
     * @return JsonResponse HTTP 200，data 为企业记录，外层包含 code、meta；记录不存在返回 404
     */
    public function show(int $id): JsonResponse
    {
        return $this->success(PlatformEnterprise::query()->findOrFail($id));
    }

    /**
     * 先创建企业类型的组织，再创建并绑定企业；整个流程在同一事务中完成。
     *
     * POST /sapi/enterprises
     *
     * JSON 请求体：
     *   code: string，编码（必填）
     *   name: string，名称（必填）
     *   admin_user_id: int，企业管理员（可选）
     *   is_enabled: bool，启用状态（可选）
     *   short_name: string，简称（可选）
     *   credit_code: string，统一社会信用代码（可选）
     *   contact_name: string，联系人（可选）
     *   contact_mobile: string，联系电话（可选）
     *   address: string，地址（可选）
     *   description: string，简介（可选）
     *   remark: string，备注（可选）
     *
     * @param  SaveEnterpriseRequest  $request  经校验的企业创建参数
     * @return JsonResponse HTTP 201，data 为创建后的企业记录，外层包含 code、meta；校验失败返回 422
     */
    public function store(SaveEnterpriseRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request): PlatformEnterprise {
            $data = $request->validated();
            $organization = PlatformOrganization::query()->create([
                ...array_intersect_key($data, array_flip([
                    'code', 'name', 'short_name', 'contact_name', 'contact_mobile',
                    'address', 'description', 'remark', 'is_enabled',
                ])),
                'primary_type' => OrganizationType::Enterprise,
            ]);

            $enterprise = PlatformEnterprise::query()->create($data);
            $enterprise->organization()->associate($organization);
            $enterprise->save();

            return $enterprise->load('organization');
        });

        return $this->success($record, 201);
    }

    /**
     * 更新企业提交的字段，未提交的字段保持原值。
     *
     * PUT /sapi/enterprises/{id}
     * PATCH /sapi/enterprises/{id}
     *
     * JSON 请求体与 store 相同，各字段仅在提交时校验；创建时必填的字段提交后仍不能为空。
     *
     * @param  SaveEnterpriseRequest  $request  经校验的企业更新参数
     * @param  int  $id  路径参数，中台企业 ID
     * @return JsonResponse HTTP 200，data 为更新后的企业记录，外层包含 code、meta；不存在返回 404，校验失败返回 422
     */
    public function update(SaveEnterpriseRequest $request, int $id): JsonResponse
    {
        $record = PlatformEnterprise::query()->findOrFail($id);
        $record->update($request->validated());

        return $this->success($record->refresh());
    }

    /**
     * 删除企业。
     *
     * DELETE /sapi/enterprises/{id}
     *
     * 请求体：无。
     *
     * @param  int  $id  路径参数，中台企业 ID
     * @return JsonResponse HTTP 200，data 为空数组，外层包含 code、meta；不存在返回 404，存在外键引用返回 409
     */
    public function destroy(int $id): JsonResponse
    {
        $record = PlatformEnterprise::query()->findOrFail($id);

        try {
            $record->delete();
        } catch (QueryException $exception) {
            if (str_starts_with((string) $exception->getCode(), '23')) {
                return $this->error('记录仍有关联数据，无法删除', 409);
            }

            throw $exception;
        }

        return $this->success();
    }
}
