<?php

namespace App\SApi\Controllers;

use App\Models\PlatformOrganization;
use App\SApi\Requests\PaginationRequest;
use App\SApi\Requests\SaveOrganizationRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

/** 中台组织管理接口。 */
class OrganizationController extends Controller
{
    /**
     * 分页查询组织。
     *
     * GET /sapi/organizations
     *
     * @param  PaginationRequest  $request  查询参数：per_page（可选，1–100，默认 20）、page（可选，默认 1）
     * @return JsonResponse HTTP 200，data 包含 data（组织列表）、current_page、per_page、total 等分页信息；外层包含 code、meta
     */
    public function index(PaginationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $records = PlatformOrganization::query()
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 20);

        return $this->success($records);
    }

    /**
     * 查询组织详情。
     *
     * GET /sapi/organizations/{id}
     *
     * @param  int  $id  路径参数，中台组织 ID
     * @return JsonResponse HTTP 200，data 为组织记录，外层包含 code、meta；记录不存在返回 404
     */
    public function show(int $id): JsonResponse
    {
        return $this->success(PlatformOrganization::query()->findOrFail($id));
    }

    /**
     * 创建组织，仅保存通过校验的字段。
     *
     * POST /sapi/organizations
     *
     * JSON 请求体：
     *   enterprise_id: int，所属企业（必填）
     *   code: string，编码（必填）
     *   name: string，名称（必填）
     *   parent_id: int，上级组织（可选）
     *   is_enabled: bool，启用状态（可选）
     *   short_name: string，简称（可选）
     *   primary_type: string，主要类型（可选）
     *   area_code: string，地区码（可选）
     *   address: string，地址（可选）
     *   logo: string，Logo（可选）
     *   website: string，官方网站（可选）
     *   contact_name: string，联系人（可选）
     *   contact_mobile: string，联系电话（可选）
     *   contact_email: string，联系邮箱（可选）
     *   description: string，简介（可选）
     *   verified_status: string，认证状态（可选）
     *   remark: string，备注（可选）
     *
     * @param  SaveOrganizationRequest  $request  经校验的组织创建参数
     * @return JsonResponse HTTP 201，data 为创建后的组织记录，外层包含 code、meta；校验失败返回 422
     */
    public function store(SaveOrganizationRequest $request): JsonResponse
    {
        return $this->success(PlatformOrganization::query()->create($request->validated()), 201);
    }

    /**
     * 更新组织提交的字段，未提交的字段保持原值。
     *
     * PUT /sapi/organizations/{id}
     * PATCH /sapi/organizations/{id}
     *
     * JSON 请求体与 store 相同，各字段仅在提交时校验；创建时必填的字段提交后仍不能为空。
     *
     * @param  SaveOrganizationRequest  $request  经校验的组织更新参数
     * @param  int  $id  路径参数，中台组织 ID
     * @return JsonResponse HTTP 200，data 为更新后的组织记录，外层包含 code、meta；不存在返回 404，校验失败返回 422
     */
    public function update(SaveOrganizationRequest $request, int $id): JsonResponse
    {
        $record = PlatformOrganization::query()->findOrFail($id);
        $record->update($request->validated());

        return $this->success($record->refresh());
    }

    /**
     * 删除组织。
     *
     * DELETE /sapi/organizations/{id}
     *
     * 请求体：无。
     *
     * @param  int  $id  路径参数，中台组织 ID
     * @return JsonResponse HTTP 200，data 为空数组，外层包含 code、meta；不存在返回 404，存在外键引用返回 409
     */
    public function destroy(int $id): JsonResponse
    {
        $record = PlatformOrganization::query()->findOrFail($id);

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
