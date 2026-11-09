# 企业接口

所有请求均需[应用签名](authentication.md)，响应使用统一 code、data、meta 结构。

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | /sapi/enterprises | 分页列表 |
| GET | /sapi/enterprises/{id} | 单条详情 |
| POST | /sapi/enterprises | 创建 |
| PUT / PATCH | /sapi/enterprises/{id} | 部分更新 |
| DELETE | /sapi/enterprises/{id} | 删除 |

## 创建入参

`POST /sapi/enterprises`，JSON 请求体：

| 字段 | 类型 | 创建必填 | 允许 null | 约束与说明 |
| --- | --- | --- | --- | --- |
| code | string | 是 | 否 | 全局唯一编码；max:255 |
| name | string | 是 | 否 | 名称；max:255 |
| admin_user_id | integer | 否 | 是 | 企业管理员，引用 users.id；exists:users,id |
| is_enabled | boolean | 否 | 否 | 启用状态，创建默认 true |
| short_name | string | 否 | 是 | 简称；max:255 |
| credit_code | string | 否 | 是 | 统一社会信用代码，当前未校验格式或唯一性；max:255 |
| contact_name | string | 否 | 是 | 联系人；max:255 |
| contact_mobile | string | 否 | 是 | 联系电话；max:255 |
| address | string | 否 | 是 | 地址；max:255 |
| description | string | 否 | 是 | 简介；max:255 |
| remark | string | 否 | 是 | 备注；max:255 |

必填约束、默认值与校验以此表为准。未列字段不会写入。

请求示例（保存为 `enterprises.json`）：

```json
{
  "code": "example_corp",
  "name": "示例科技有限公司",
  "short_name": "示例科技",
  "credit_code": "EXAMPLE-CREDIT-CODE",
  "admin_user_id": 1,
  "contact_name": "张三",
  "contact_mobile": "13800138000",
  "address": "上海市示例路 1 号",
  "description": "示例企业",
  "remark": "接口联调",
  "is_enabled": true
}
```

```bash
php sapi-client.php POST /sapi/enterprises enterprises.json
```

HTTP 201 响应示例：

```json
{
  "code": 201,
  "data": {
    "code": "example_corp",
    "name": "示例科技有限公司",
    "admin_user_id": 1,
    "is_enabled": true,
    "short_name": "示例科技",
    "credit_code": "EXAMPLE-CREDIT-CODE",
    "contact_name": "张三",
    "contact_mobile": "13800138000",
    "address": "上海市示例路 1 号",
    "description": "示例企业",
    "remark": "接口联调",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "created_at": "2026-11-09T16:23:46.000000Z",
    "id": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 响应记录字段

列表的 `data.data[]`、详情/创建/更新的 `data` 使用以下记录结构。密码和 remember_token 永不返回，关联对象不自动展开。

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| id | integer | 记录主键 |
| code | string | 全局唯一编码 |
| name | string | 名称 |
| is_enabled | boolean | 启用状态，创建默认 true |
| created_at | string | 创建时间 |
| updated_at | string | 更新时间 |
| short_name | string / null | 简称 |
| credit_code | string / null | 统一社会信用代码，当前未校验格式或唯一性 |
| contact_name | string / null | 联系人 |
| contact_mobile | string / null | 联系电话 |
| address | string / null | 地址 |
| description | string / null | 简介 |
| remark | string / null | 备注 |
| admin_user_id | integer / null | 企业管理员，引用 users.id |

管理员可以为空；设置 admin_user_id 不会自动创建职工关联。企业可以分别绑定多个应用的套餐，见[企业应用套餐](subscriptions.md)；职工见[企业职工](employees.md)。

## 分页列表

`GET /sapi/enterprises?page=1&per_page=20`。无请求体，page、per_page 说明见[公共约定](authentication.md)。

```bash
php sapi-client.php GET '/sapi/enterprises?page=1&per_page=20'
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "code": "example_corp",
        "name": "示例科技有限公司",
        "is_enabled": true,
        "created_at": "2026-11-09T16:23:46.000000Z",
        "updated_at": "2026-11-09T16:23:46.000000Z",
        "short_name": "示例科技",
        "credit_code": "EXAMPLE-CREDIT-CODE",
        "contact_name": "张三",
        "contact_mobile": "13800138000",
        "address": "上海市示例路 1 号",
        "description": "示例企业",
        "remark": "接口联调",
        "admin_user_id": 1
      }
    ],
    "first_page_url": "http://localhost:8000/sapi/enterprises?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/sapi/enterprises?page=1",
    "links": [
      {
        "url": null,
        "label": "pagination.previous",
        "page": null,
        "active": false
      },
      {
        "url": "http://localhost:8000/sapi/enterprises?page=1",
        "label": "1",
        "page": 1,
        "active": true
      },
      {
        "url": null,
        "label": "pagination.next",
        "page": null,
        "active": false
      }
    ],
    "next_page_url": null,
    "path": "http://localhost:8000/sapi/enterprises",
    "per_page": 20,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 详情

`GET /sapi/enterprises/{id}`。id 为中台企业主键（integer），不是编码或 UUID，无请求体。

```bash
php sapi-client.php GET /sapi/enterprises/1
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "code": "example_corp",
    "name": "示例科技有限公司",
    "is_enabled": true,
    "created_at": "2026-11-09T16:23:46.000000Z",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "short_name": "示例科技",
    "credit_code": "EXAMPLE-CREDIT-CODE",
    "contact_name": "张三",
    "contact_mobile": "13800138000",
    "address": "上海市示例路 1 号",
    "description": "示例企业",
    "remark": "接口联调",
    "admin_user_id": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 更新

`PATCH /sapi/enterprises/{id}` 或 `PUT /sapi/enterprises/{id}`。路径 id 同详情，JSON 字段同创建表，全部按需提交。未提交字段不变，nullable 字段可用 null 清空。

请求示例（保存为 `update.json`）：

```json
{
  "name": "更新后的名称"
}
```

```bash
php sapi-client.php PATCH /sapi/enterprises/1 update.json
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "code": "example_corp",
    "name": "更新后的名称",
    "is_enabled": true,
    "created_at": "2026-11-09T16:23:46.000000Z",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "short_name": "示例科技",
    "credit_code": "EXAMPLE-CREDIT-CODE",
    "contact_name": "张三",
    "contact_mobile": "13800138000",
    "address": "上海市示例路 1 号",
    "description": "示例企业",
    "remark": "接口联调",
    "admin_user_id": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 删除

`DELETE /sapi/enterprises/{id}`，id 同详情，无请求体。

```bash
php sapi-client.php DELETE /sapi/enterprises/1
```

HTTP 200：

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

此操作为物理删除，仍有外键引用（如子组织、职工、套餐等）时返回 409，需先处理关联数据。

## 错误

记录不存在返回 404；类型、唯一性、外键和必填校验失败返回 422；认证/重放/限流错误见[公共约定](authentication.md)。

```json
{
  "code": 404,
  "message": "请求的资源不存在"
}
```
