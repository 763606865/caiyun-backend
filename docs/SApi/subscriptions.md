# 企业应用套餐

所有请求均需[签名鉴权](authentication.md)。路径 `enterprise` 为中台企业 ID，`id` 为本关联记录 ID。
复用应用管理中注册的应用，每个企业可同时绑定 OA、CRM、ERP 等多个应用，各应用有各自套餐。套餐为名称字段，暂未建设统一套餐目录、计费或权益自动下发。额度仅记录，不自动限制企业职工数；有效期和启用状态不会改变调用方应用鉴权。

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | /sapi/enterprises/{enterprise}/subscriptions | 分页列表 |
| POST | /sapi/enterprises/{enterprise}/subscriptions | 新增 |
| GET | /sapi/enterprises/{enterprise}/subscriptions/{id} | 详情 |
| PUT / PATCH | /sapi/enterprises/{enterprise}/subscriptions/{id} | 部分更新 |
| DELETE | /sapi/enterprises/{enterprise}/subscriptions/{id} | 删除关联 |

## 创建入参

| 字段 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| application_id | integer | 是 | 应用管理中的应用 ID；企业内唯一，每个应用一个套餐 |
| package_name | string | 是 | 套餐名称，最长 255；例如旗舰版、基础版、进阶版 |
| seat_limit | integer | 是 | 用户额度，1–4294967295 |
| starts_at | date string | 是 | 套餐开始时间，建议 ISO 8601 含时区 |
| ends_at | date string | 是 | 套餐结束时间，必须晚于开始时间 |
| is_enabled | boolean | 否 | 默认 true，启停状态 |

POST 请求示例（保存为 `subscriptions.json`）：

```json
{
  "application_id": 4,
  "package_name": "进阶版",
  "seat_limit": 100,
  "starts_at": "2026-01-01T00:00:00+08:00",
  "ends_at": "2027-01-01T00:00:00+08:00"
}
```

```bash
php sapi-client.php POST /sapi/enterprises/1/subscriptions subscriptions.json
```

HTTP 201 示例：

```json
{
  "code": 201,
  "data": {
    "id": 3,
    "application_id": 4,
    "enterprise_id": 1,
    "package_name": "进阶版",
    "seat_limit": 100,
    "starts_at": "2026-01-01T00:00:00.000000Z",
    "ends_at": "2027-01-01T00:00:00.000000Z",
    "is_enabled": true,
    "created_at": "2026-11-09T16:29:49.000000Z",
    "updated_at": "2026-11-09T16:29:49.000000Z",
    "application": {
      "id": 4,
      "code": "ERP",
      "name": "ERP"
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
| application_id | integer | 应用管理中的应用 ID；企业内唯一，每个应用一个套餐 |
| package_name | string | 套餐名称，最长 255；例如旗舰版、基础版、进阶版 |
| seat_limit | integer | 用户额度，1–4294967295 |
| starts_at | date string | 套餐开始时间，建议 ISO 8601 含时区 |
| ends_at | date string | 套餐结束时间，必须晚于开始时间 |
| is_enabled | boolean | 默认 true，启停状态 |
| created_at / updated_at | string | 创建 / 更新时间 |
| application | object | 仅包含 id、code、name，不包含 APP_KEY 或 APP_SECRET |

## 列表与详情

```bash
php sapi-client.php GET '/sapi/enterprises/1/subscriptions?page=1&per_page=20'
php sapi-client.php GET /sapi/enterprises/1/subscriptions/3
```

无请求体；分页参数与分页字段见[公共约定](authentication.md)。详情 HTTP 200 的 data 与创建响应同结构；列表为分页对象，其中 data.data[] 为上述记录。

详情响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 3,
    "application_id": 4,
    "enterprise_id": 1,
    "package_name": "进阶版",
    "seat_limit": 100,
    "starts_at": "2026-01-01T00:00:00.000000Z",
    "ends_at": "2027-01-01T00:00:00.000000Z",
    "is_enabled": true,
    "created_at": "2026-11-09T16:29:49.000000Z",
    "updated_at": "2026-11-09T16:29:49.000000Z",
    "application": {
      "id": 4,
      "code": "ERP",
      "name": "ERP"
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
        "id": 3,
        "application_id": 4,
        "enterprise_id": 1,
        "package_name": "进阶版",
        "seat_limit": 100,
        "starts_at": "2026-01-01T00:00:00.000000Z",
        "ends_at": "2027-01-01T00:00:00.000000Z",
        "is_enabled": true,
        "created_at": "2026-11-09T16:29:49.000000Z",
        "updated_at": "2026-11-09T16:29:49.000000Z",
        "application": {
          "id": 4,
          "code": "ERP",
          "name": "ERP"
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
  "package_name": "旗舰版",
  "seat_limit": 200
}
```

```bash
php sapi-client.php PATCH /sapi/enterprises/1/subscriptions/3 update.json
```

HTTP 200 返回更新后记录：

```json
{
  "code": 200,
  "data": {
    "id": 3,
    "application_id": 4,
    "enterprise_id": 1,
    "package_name": "旗舰版",
    "seat_limit": 200,
    "starts_at": "2026-01-01T00:00:00.000000Z",
    "ends_at": "2027-01-01T00:00:00.000000Z",
    "is_enabled": true,
    "created_at": "2026-11-09T16:29:49.000000Z",
    "updated_at": "2026-11-09T16:29:49.000000Z",
    "application": {
      "id": 4,
      "code": "ERP",
      "name": "ERP"
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
php sapi-client.php DELETE /sapi/enterprises/1/subscriptions/3
```

无请求体，物理删除当前关联，保留企业及应用。HTTP 200：

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
  "message": "应用已存在"
}
```

同一企业的示例配置：OA → 旗舰版、CRM → 基础版、ERP → 进阶版。分别 POST 三条记录，application_id 使用各自实际应用 ID。

## 后台维护

Admin → 中台管理 → 企业管理 → 编辑企业，下方提供“职工”“应用套餐”页签，可新增、编辑、移除当前企业的关联。仅超级管理员可访问该企业管理入口。
