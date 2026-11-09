# 用户接口

所有请求均需[应用签名](authentication.md)，响应使用统一 code、data、meta 结构。

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | /sapi/users | 分页列表 |
| GET | /sapi/users/{id} | 单条详情 |
| POST | /sapi/users | 创建 |
| PUT / PATCH | /sapi/users/{id} | 部分更新 |
| DELETE | /sapi/users/{id} | 删除 |

## 创建入参

`POST /sapi/users`，JSON 请求体：

| 字段 | 类型 | 创建必填 | 允许 null | 约束与说明 |
| --- | --- | --- | --- | --- |
| real_name | string | 否 | 是 | 真实姓名；max:255 |
| nick_name | string | 否 | 是 | 昵称；max:255 |
| phone | string | 是 | 否 | 手机号，全局唯一（包含已软删除记录）；max:32 |
| email | string | 否 | 是 | 邮箱地址，当前不校验唯一性；max:255 |
| password | string | 是 | 否 | 登录密码，写入时哈希保存，不返回；min:8；max:255 |
| status | integer | 否 | 否 | 状态：0 禁用、1 正常，创建默认 1；in:0,1 |

必填约束、默认值与校验以此表为准。未列字段不会写入。

请求示例（保存为 `users.json`）：

```json
{
  "real_name": "张三",
  "nick_name": "张三",
  "phone": "13800138000",
  "email": "zhangsan@example.com",
  "password": "ExamplePass123!",
  "status": 1
}
```

```bash
php sapi-client.php POST /sapi/users users.json
```

HTTP 201 响应示例：

```json
{
  "code": 201,
  "data": {
    "real_name": "张三",
    "nick_name": "张三",
    "phone": "13800138000",
    "email": "zhangsan@example.com",
    "status": 1,
    "uuid": "01a1c11f-756a-700b-be34-989e8b2eca73",
    "gender": 0,
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
| uuid | string | 全局 UUID，自动生成 |
| real_name | string / null | 真实姓名 |
| nick_name | string / null | 昵称 |
| phone | string | 手机号，全局唯一（包含已软删除记录） |
| avatar | string / null | 头像路径 |
| gender | integer | 性别：0 未知、1 男、2 女 |
| email | string / null | 邮箱地址，当前不校验唯一性 |
| email_verified_at | string / null | 邮箱验证时间 |
| status | integer | 状态：0 禁用、1 正常，创建默认 1 |
| created_at | string | 创建时间 |
| updated_at | string | 更新时间 |
| deleted_at | string / null | 软删除时间；正常记录为 null |
| source | string / null | 用户来源，当前接口不写入 |
| last_login_at | string / null | 最近登录时间，当前接口不更新 |

## 分页列表

`GET /sapi/users?page=1&per_page=20`。无请求体，page、per_page 说明见[公共约定](authentication.md)。

```bash
php sapi-client.php GET '/sapi/users?page=1&per_page=20'
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
        "uuid": "01a1c11f-756a-700b-be34-989e8b2eca73",
        "real_name": "张三",
        "nick_name": "张三",
        "phone": "13800138000",
        "avatar": null,
        "gender": 0,
        "email": "zhangsan@example.com",
        "email_verified_at": null,
        "status": 1,
        "created_at": "2026-11-09T16:23:46.000000Z",
        "updated_at": "2026-11-09T16:23:46.000000Z",
        "deleted_at": null,
        "source": null,
        "last_login_at": null
      }
    ],
    "first_page_url": "http://localhost:8000/sapi/users?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/sapi/users?page=1",
    "links": [
      {
        "url": null,
        "label": "pagination.previous",
        "page": null,
        "active": false
      },
      {
        "url": "http://localhost:8000/sapi/users?page=1",
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
    "path": "http://localhost:8000/sapi/users",
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

`GET /sapi/users/{id}`。id 为中台用户主键（integer），不是编码或 UUID，无请求体。

```bash
php sapi-client.php GET /sapi/users/1
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "uuid": "01a1c11f-756a-700b-be34-989e8b2eca73",
    "real_name": "张三",
    "nick_name": "张三",
    "phone": "13800138000",
    "avatar": null,
    "gender": 0,
    "email": "zhangsan@example.com",
    "email_verified_at": null,
    "status": 1,
    "created_at": "2026-11-09T16:23:46.000000Z",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "deleted_at": null,
    "source": null,
    "last_login_at": null
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 更新

`PATCH /sapi/users/{id}` 或 `PUT /sapi/users/{id}`。路径 id 同详情，JSON 字段同创建表，全部按需提交。未提交字段不变，nullable 字段可用 null 清空。

请求示例（保存为 `update.json`）：

```json
{
  "nick_name": "张三（已更新）"
}
```

```bash
php sapi-client.php PATCH /sapi/users/1 update.json
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "uuid": "01a1c11f-756a-700b-be34-989e8b2eca73",
    "real_name": "张三",
    "nick_name": "张三（已更新）",
    "phone": "13800138000",
    "avatar": null,
    "gender": 0,
    "email": "zhangsan@example.com",
    "email_verified_at": null,
    "status": 1,
    "created_at": "2026-11-09T16:23:46.000000Z",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "deleted_at": null,
    "source": null,
    "last_login_at": null
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 删除

`DELETE /sapi/users/{id}`，id 同详情，无请求体。

```bash
php sapi-client.php DELETE /sapi/users/1
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

用户为软删除，之后通过 SAPI 列表/详情不可见，手机号仍受唯一性约束；不会自动解除企业职工关联。

## 错误

记录不存在返回 404；类型、唯一性、外键和必填校验失败返回 422；认证/重放/限流错误见[公共约定](authentication.md)。

```json
{
  "code": 404,
  "message": "请求的资源不存在"
}
```
