# 企业职工

所有请求均需[签名鉴权](authentication.md)。路径 `enterprise` 为中台企业 ID，`id` 为本关联记录 ID。
同一用户可加入多个企业；删除职工关联不会删除用户。离职可以设置 is_enabled=false。企业管理员字段与职工关联独立维护。

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | /sapi/enterprises/{enterprise}/employees | 分页列表 |
| POST | /sapi/enterprises/{enterprise}/employees | 新增 |
| GET | /sapi/enterprises/{enterprise}/employees/{id} | 详情 |
| PUT / PATCH | /sapi/enterprises/{enterprise}/employees/{id} | 部分更新 |
| DELETE | /sapi/enterprises/{enterprise}/employees/{id} | 删除关联 |

## 创建入参

| 字段 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| user_id | integer | 是 | 已有未软删除用户的 ID；同一企业不能重复关联同一用户 |
| employee_no | string / null | 否 | 企业内唯一职工编号，最长 255，可空 |
| position | string / null | 否 | 职务，最长 255 |
| is_enabled | boolean | 否 | 在职启用状态，默认 true |

POST 请求示例（保存为 `employees.json`）：

```json
{
  "user_id": 1,
  "employee_no": "E001",
  "position": "工程师",
  "is_enabled": true
}
```

```bash
php sapi-client.php POST /sapi/enterprises/1/employees employees.json
```

HTTP 201 示例：

```json
{
  "code": 201,
  "data": {
    "id": 1,
    "enterprise_id": 1,
    "user_id": 1,
    "employee_no": "E001",
    "position": "工程师",
    "is_enabled": true,
    "created_at": "2026-11-09T16:29:49.000000Z",
    "updated_at": "2026-11-09T16:29:49.000000Z",
    "user": {
      "id": 1,
      "uuid": "01a1c124-ff13-700a-a4a1-14ff21a0597a",
      "real_name": null,
      "nick_name": null,
      "phone": "13800138002",
      "avatar": null,
      "gender": 0,
      "email": null,
      "email_verified_at": null,
      "status": 1,
      "created_at": "2026-11-09T16:29:49.000000Z",
      "updated_at": "2026-11-09T16:29:49.000000Z",
      "deleted_at": null,
      "source": null,
      "last_login_at": null
    }
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 响应字段

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| id | integer | 本关联记录主键，用于详情/更新/删除路径 |
| enterprise_id | integer | 所属企业 ID，由路径决定，请求体不可覆盖 |
| user_id | integer | 已有未软删除用户的 ID；同一企业不能重复关联同一用户 |
| employee_no | string / null | 企业内唯一职工编号，最长 255，可空 |
| position | string / null | 职务，最长 255 |
| is_enabled | boolean | 在职启用状态，默认 true |
| created_at / updated_at | string | 创建 / 更新时间 |
| user | object / null | 用户公开记录，字段见[用户](users.md)，不含密码；若用户之后软删除，此处为 null |

## 列表与详情

```bash
php sapi-client.php GET '/sapi/enterprises/1/employees?page=1&per_page=20'
php sapi-client.php GET /sapi/enterprises/1/employees/1
```

无请求体；分页参数与分页字段见[公共约定](authentication.md)。详情 HTTP 200 的 data 与创建响应同结构；列表为分页对象，其中 data.data[] 为上述记录。

详情响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "enterprise_id": 1,
    "user_id": 1,
    "employee_no": "E001",
    "position": "工程师",
    "is_enabled": true,
    "created_at": "2026-11-09T16:29:49.000000Z",
    "updated_at": "2026-11-09T16:29:49.000000Z",
    "user": {
      "id": 1,
      "uuid": "01a1c124-ff13-700a-a4a1-14ff21a0597a",
      "real_name": null,
      "nick_name": null,
      "phone": "13800138002",
      "avatar": null,
      "gender": 0,
      "email": null,
      "email_verified_at": null,
      "status": 1,
      "created_at": "2026-11-09T16:29:49.000000Z",
      "updated_at": "2026-11-09T16:29:49.000000Z",
      "deleted_at": null,
      "source": null,
      "last_login_at": null
    }
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

列表响应示例（节选，完整分页字段见公共约定）：

```json
{
  "code": 200,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "enterprise_id": 1,
        "user_id": 1,
        "employee_no": "E001",
        "position": "工程师",
        "is_enabled": true,
        "created_at": "2026-11-09T16:29:49.000000Z",
        "updated_at": "2026-11-09T16:29:49.000000Z",
        "user": {
          "id": 1,
          "uuid": "01a1c124-ff13-700a-a4a1-14ff21a0597a",
          "real_name": null,
          "nick_name": null,
          "phone": "13800138002",
          "avatar": null,
          "gender": 0,
          "email": null,
          "email_verified_at": null,
          "status": 1,
          "created_at": "2026-11-09T16:29:49.000000Z",
          "updated_at": "2026-11-09T16:29:49.000000Z",
          "deleted_at": null,
          "source": null,
          "last_login_at": null
        }
      }
    ],
    "per_page": 20,
    "total": 1,
    "last_page": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 更新

PUT/PATCH 的入参同创建表，只校验提交字段；路径 enterprise、id 必填。更新套餐任一时间会与数据库中另一端时间合并检查结束晚于开始。

保存为 `update.json`：

```json
{
  "position": "研发主管",
  "is_enabled": true
}
```

```bash
php sapi-client.php PATCH /sapi/enterprises/1/employees/1 update.json
```

HTTP 200 返回更新后记录：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "enterprise_id": 1,
    "user_id": 1,
    "employee_no": "E001",
    "position": "研发主管",
    "is_enabled": true,
    "created_at": "2026-11-09T16:29:49.000000Z",
    "updated_at": "2026-11-09T16:29:49.000000Z",
    "user": {
      "id": 1,
      "uuid": "01a1c124-ff13-700a-a4a1-14ff21a0597a",
      "real_name": null,
      "nick_name": null,
      "phone": "13800138002",
      "avatar": null,
      "gender": 0,
      "email": null,
      "email_verified_at": null,
      "status": 1,
      "created_at": "2026-11-09T16:29:49.000000Z",
      "updated_at": "2026-11-09T16:29:49.000000Z",
      "deleted_at": null,
      "source": null,
      "last_login_at": null
    }
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 删除

```bash
php sapi-client.php DELETE /sapi/enterprises/1/employees/1
```

无请求体，物理删除当前关联，保留企业及用户。HTTP 200：

```json
{
  "code": 200,
  "data": [],
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 错误与边界

企业不存在或记录不属于路径企业时返回 404。重复关联返回 422，不自动覆盖；需要使用 PATCH 更新。参数验证在控制器查找记录前执行，因此请求体本身无效时可能先返回 422。
```json
{
  "code": 422,
  "message": "职工用户已存在"
}
```

## 后台维护

Admin → 中台管理 → 企业管理 → 编辑企业，下方提供“职工”“应用套餐”页签，可新增、编辑、移除当前企业的关联。仅超级管理员可访问该企业管理入口。
