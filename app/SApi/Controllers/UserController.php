<?php

namespace App\SApi\Controllers;

use App\Models\User;
use App\SApi\Requests\PaginationRequest;
use App\SApi\Requests\SaveUserRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

/** 中台用户管理接口。 */
class UserController extends Controller
{
    /**
     * 分页查询用户。
     *
     * GET /sapi/users
     *
     * @param  PaginationRequest  $request  查询参数：per_page（可选，1–100，默认 20）、page（可选，默认 1）
     * @return JsonResponse HTTP 200，data 包含 data（用户列表）、current_page、per_page、total 等分页信息；外层包含 code、meta
     */
    public function index(PaginationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $records = User::query()
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 20);

        return $this->success($records);
    }

    /**
     * 查询用户详情。
     *
     * GET /sapi/users/{id}
     *
     * @param  int  $id  路径参数，中台用户 ID
     * @return JsonResponse HTTP 200，data 为用户记录，外层包含 code、meta；记录不存在返回 404
     */
    public function show(int $id): JsonResponse
    {
        return $this->success(User::query()->findOrFail($id));
    }

    /**
     * 创建用户，仅保存通过校验的字段。
     *
     * POST /sapi/users
     *
     * JSON 请求体：
     *   real_name: string，真实姓名（可选）
     *   nick_name: string，昵称（可选）
     *   phone: string，手机号（必填）
     *   email: string，邮箱（可选）
     *   password: string，密码（必填）
     *   status: int，用户状态（可选）
     *
     * @param  SaveUserRequest  $request  经校验的用户创建参数
     * @return JsonResponse HTTP 201，data 为创建后的用户记录，外层包含 code、meta；校验失败返回 422
     */
    public function store(SaveUserRequest $request): JsonResponse
    {
        return $this->success(User::query()->create($request->validated()), 201);
    }

    /**
     * 更新用户提交的字段，未提交的字段保持原值。
     *
     * PUT /sapi/users/{id}
     * PATCH /sapi/users/{id}
     *
     * JSON 请求体与 store 相同，各字段仅在提交时校验；创建时必填的字段提交后仍不能为空。
     *
     * @param  SaveUserRequest  $request  经校验的用户更新参数
     * @param  int  $id  路径参数，中台用户 ID
     * @return JsonResponse HTTP 200，data 为更新后的用户记录，外层包含 code、meta；不存在返回 404，校验失败返回 422
     */
    public function update(SaveUserRequest $request, int $id): JsonResponse
    {
        $record = User::query()->findOrFail($id);
        $record->update($request->validated());

        return $this->success($record->refresh());
    }

    /**
     * 删除用户。用户采用软删除。
     *
     * DELETE /sapi/users/{id}
     *
     * 请求体：无。
     *
     * @param  int  $id  路径参数，中台用户 ID
     * @return JsonResponse HTTP 200，data 为空数组，外层包含 code、meta；不存在返回 404，存在外键引用返回 409
     */
    public function destroy(int $id): JsonResponse
    {
        $record = User::query()->findOrFail($id);

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
