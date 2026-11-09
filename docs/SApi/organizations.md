# 组织接口

所有请求均需[应用签名](authentication.md)，响应使用统一 code、data、meta 结构。

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | /sapi/organizations | 分页列表 |
| GET | /sapi/organizations/{id} | 单条详情 |
| POST | /sapi/organizations | 创建 |
| PUT / PATCH | /sapi/organizations/{id} | 部分更新 |
| DELETE | /sapi/organizations/{id} | 删除 |

## 创建入参

`POST /sapi/organizations`，JSON 请求体：

| 字段 | 类型 | 创建必填 | 允许 null | 约束与说明 |
| --- | --- | --- | --- | --- |
| enterprise_id | integer | 是 | 否 | 所属企业 ID；exists:platform_enterprises,id |
| code | string | 是 | 否 | 全局唯一编码；max:255 |
| name | string | 是 | 否 | 名称；max:255 |
| parent_id | integer | 否 | 是 | 上级组织 ID；null 为顶级；exists:platform_organizations,id |
| is_enabled | boolean | 否 | 否 | 启用状态，创建默认 true |
| short_name | string | 否 | 是 | 简称；max:255 |
| primary_type | string | 否 | 是 | 主要类型，当前为自由字符串，无固定枚举；max:255 |
| area_code | string | 否 | 是 | 地区码，当前不检查地区字典；max:255 |
| address | string | 否 | 是 | 地址；max:255 |
| logo | string | 否 | 是 | Logo 地址，当前仅校验字符串；max:255 |
| website | string | 否 | 是 | 官方网站，当前仅校验字符串；max:255 |
| contact_name | string | 否 | 是 | 联系人；max:255 |
| contact_mobile | string | 否 | 是 | 联系电话；max:255 |
| contact_email | string | 否 | 是 | 联系邮箱，当前仅校验字符串；max:255 |
| description | string | 否 | 是 | 简介；max:255 |
| verified_status | string | 否 | 是 | 认证状态，当前为自由字符串，无固定枚举；max:255 |
| remark | string | 否 | 是 | 备注；max:255 |

必填约束、默认值与校验以此表为准。未列字段不会写入。

请求示例（保存为 `organizations.json`）：

```json
{
  "enterprise_id": 1,
  "code": "example_department",
  "name": "研发部",
  "parent_id": null,
  "short_name": "研发",
  "primary_type": "department",
  "area_code": "310000",
  "address": "上海市示例路 1 号",
  "logo": "https://example.com/logo.png",
  "website": "https://example.com",
  "contact_name": "张三",
  "contact_mobile": "13800138000",
  "contact_email": "contact@example.com",
  "description": "产品研发组织",
  "verified_status": "unverified",
  "remark": "接口联调",
  "is_enabled": true
}
```

```bash
php sapi-client.php POST /sapi/organizations organizations.json
```

HTTP 201 响应示例：

```json
{
  "code": 201,
  "data": {
    "enterprise_id": 1,
    "code": "example_department",
    "name": "研发部",
    "parent_id": null,
    "is_enabled": true,
    "short_name": "研发",
    "primary_type": "department",
    "area_code": "310000",
    "address": "上海市示例路 1 号",
    "logo": "https://example.com/logo.png",
    "website": "https://example.com",
    "contact_name": "张三",
    "contact_mobile": "13800138000",
    "contact_email": "contact@example.com",
    "description": "产品研发组织",
    "verified_status": "unverified",
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
| enterprise_id | integer | 所属企业 ID |
| code | string | 全局唯一编码 |
| name | string | 名称 |
| created_at | string | 创建时间 |
| updated_at | string | 更新时间 |
| short_name | string / null | 简称 |
| primary_type | string / null | 主要类型，当前为自由字符串，无固定枚举 |
| area_code | string / null | 地区码，当前不检查地区字典 |
| address | string / null | 地址 |
| logo | string / null | Logo 地址，当前仅校验字符串 |
| website | string / null | 官方网站，当前仅校验字符串 |
| contact_name | string / null | 联系人 |
| contact_mobile | string / null | 联系电话 |
| contact_email | string / null | 联系邮箱，当前仅校验字符串 |
| description | string / null | 简介 |
| verified_status | string / null | 认证状态，当前为自由字符串，无固定枚举 |
| remark | string / null | 备注 |
| parent_id | integer / null | 上级组织 ID；null 为顶级 |
| is_enabled | integer / boolean | 启用状态，创建默认 true |

组织 is_enabled 未显式设置 boolean cast，详情可能返回 0/1（SQLite 示例），创建时提交布尔值也可能直接返回布尔值。组织没有固定类型/认证状态枚举；更新 parent_id 时会检查是否形成循环，目前不限制上级组织所属企业。

## 分页列表

`GET /sapi/organizations?page=1&per_page=20`。无请求体，page、per_page 说明见[公共约定](authentication.md)。

```bash
php sapi-client.php GET '/sapi/organizations?page=1&per_page=20'
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
        "enterprise_id": 1,
        "code": "example_department",
        "name": "研发部",
        "created_at": "2026-11-09T16:23:46.000000Z",
        "updated_at": "2026-11-09T16:23:46.000000Z",
        "short_name": "研发",
        "primary_type": "department",
        "area_code": "310000",
        "address": "上海市示例路 1 号",
        "logo": "https://example.com/logo.png",
        "website": "https://example.com",
        "contact_name": "张三",
        "contact_mobile": "13800138000",
        "contact_email": "contact@example.com",
        "description": "产品研发组织",
        "verified_status": "unverified",
        "remark": "接口联调",
        "parent_id": null,
        "is_enabled": 1
      }
    ],
    "first_page_url": "http://localhost:8000/sapi/organizations?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/sapi/organizations?page=1",
    "links": [
      {
        "url": null,
        "label": "pagination.previous",
        "page": null,
        "active": false
      },
      {
        "url": "http://localhost:8000/sapi/organizations?page=1",
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
    "path": "http://localhost:8000/sapi/organizations",
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

`GET /sapi/organizations/{id}`。id 为中台组织主键（integer），不是编码或 UUID，无请求体。

```bash
php sapi-client.php GET /sapi/organizations/1
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "enterprise_id": 1,
    "code": "example_department",
    "name": "研发部",
    "created_at": "2026-11-09T16:23:46.000000Z",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "short_name": "研发",
    "primary_type": "department",
    "area_code": "310000",
    "address": "上海市示例路 1 号",
    "logo": "https://example.com/logo.png",
    "website": "https://example.com",
    "contact_name": "张三",
    "contact_mobile": "13800138000",
    "contact_email": "contact@example.com",
    "description": "产品研发组织",
    "verified_status": "unverified",
    "remark": "接口联调",
    "parent_id": null,
    "is_enabled": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 更新

`PATCH /sapi/organizations/{id}` 或 `PUT /sapi/organizations/{id}`。路径 id 同详情，JSON 字段同创建表，全部按需提交。未提交字段不变，nullable 字段可用 null 清空。

请求示例（保存为 `update.json`）：

```json
{
  "name": "更新后的名称"
}
```

```bash
php sapi-client.php PATCH /sapi/organizations/1 update.json
```

HTTP 200 响应示例：

```json
{
  "code": 200,
  "data": {
    "id": 1,
    "enterprise_id": 1,
    "code": "example_department",
    "name": "更新后的名称",
    "created_at": "2026-11-09T16:23:46.000000Z",
    "updated_at": "2026-11-09T16:23:46.000000Z",
    "short_name": "研发",
    "primary_type": "department",
    "area_code": "310000",
    "address": "上海市示例路 1 号",
    "logo": "https://example.com/logo.png",
    "website": "https://example.com",
    "contact_name": "张三",
    "contact_mobile": "13800138000",
    "contact_email": "contact@example.com",
    "description": "产品研发组织",
    "verified_status": "unverified",
    "remark": "接口联调",
    "parent_id": null,
    "is_enabled": 1
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

## 删除

`DELETE /sapi/organizations/{id}`，id 同详情，无请求体。

```bash
php sapi-client.php DELETE /sapi/organizations/1
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
