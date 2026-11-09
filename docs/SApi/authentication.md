# 接入、签名与公共约定

## 请求地址与凭证

Base URL 示例：`https://center.example.com`。所有地址均以 `/sapi` 开头，使用 HTTPS。
在 Admin 应用管理中创建应用，取得 APP_KEY、APP_SECRET，并启用应用。预置应用默认停用。
APP_KEY 是公开标识；APP_SECRET 仅用于服务端签名，不作为参数或请求头传输。

| 请求头 | 必填 | 说明 |
| --- | --- | --- |
| Accept | 建议 | `application/json` |
| Content-Type | 有 JSON 请求体时 | `application/json` |
| X-App-Key | 是 | 后台生成的 APP_KEY |
| X-Timestamp | 是 | Unix 秒时间戳，十进制数字字符串，与中台时间差不超过 300 秒 |
| X-Nonce | 是 | 每次请求随机生成，16–128 位，允许字母、数字、下划线和横线 |
| X-Signature | 是 | 以下算法生成的小写十六进制 HMAC-SHA256 |

## 签名算法

以下五项以单个 `\n` 连接，末尾不额外换行：

```text
大写 HTTP 方法
原始 URI（路径及查询串）
X-Timestamp
X-Nonce
原始请求体的 SHA256 小写十六进制摘要
```

`signature = hex(HMAC-SHA256(canonical, APP_SECRET))`。
URI 不包含协议、主机和端口；例如 `/sapi/users?page=1&per_page=20`。查询参数顺序、编码、JSON 空格和字段顺序必须与实际发送字节一致。
空请求体的摘要为 `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`。不要将空请求体自动改为 `{}` 或 `[]` 后再发送。

同一 APP_KEY 的 nonce 在 600 秒内不能重复。重试需要新时间戳、新 nonce 和新签名；这不是业务幂等键。认证通过后即占用 nonce，即使后续参数校验失败也必须更换。生产多实例需要共享缓存。
应用停用、凭证过期时返回 401；配置 IP 白名单时验证客户端 IP，白名单为空表示不限制。

## 可执行 PHP 调用示例

保存为本地 `sapi-client.php`，需要 PHP cURL 扩展。通过环境变量注入凭证；请勿把真实密钥写入代码库。

```php
<?php
$baseUrl = rtrim((string) getenv('SAPI_BASE_URL'), '/');
$appKey = (string) getenv('SAPI_APP_KEY');
$appSecret = (string) getenv('SAPI_APP_SECRET');
$method = strtoupper($argv[1] ?? 'GET');
$uri = $argv[2] ?? '/sapi/ping';
// 原样签名和发送，不重新序列化 JSON。
$body = isset($argv[3]) ? file_get_contents($argv[3]) : '';
if ($body === false || $baseUrl === '' || $appKey === '' || $appSecret === '') {
    throw new RuntimeException('请检查环境变量和请求体文件');
}
$timestamp = (string) time();
$nonce = bin2hex(random_bytes(16));
$canonical = implode("\n", [
    $method,
    $uri,
    $timestamp,
    $nonce,
    hash('sha256', $body),
]);
$signature = hash_hmac('sha256', $canonical, $appSecret);
$headers = [
    'Accept: application/json',
    'Content-Type: application/json',
    'X-App-Key: '.$appKey,
    'X-Timestamp: '.$timestamp,
    'X-Nonce: '.$nonce,
    'X-Signature: '.$signature,
];
$handle = curl_init($baseUrl.$uri);
$options = [
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
];
if ($body !== '') {
    $options[CURLOPT_POSTFIELDS] = $body;
}
curl_setopt_array($handle, $options);
$response = curl_exec($handle);
if ($response === false) {
    throw new RuntimeException(curl_error($handle));
}
echo 'HTTP '.curl_getinfo($handle, CURLINFO_HTTP_CODE).PHP_EOL;
echo $response.PHP_EOL;
curl_close($handle);
```

```bash
export SAPI_BASE_URL='https://center.example.com'
export SAPI_APP_KEY='替换为实际 APP_KEY'
# SAPI_APP_SECRET 请通过本地安全环境变量注入
php sapi-client.php GET /sapi/ping
php sapi-client.php GET '/sapi/users?page=1&per_page=20'
php sapi-client.php POST /sapi/users user.json
php sapi-client.php PATCH /sapi/users/1 update.json
php sapi-client.php DELETE /sapi/users/1
```

## 成功响应

```json
{
  "code": 200,
  "data": {},
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| code | integer | 与 HTTP 状态码一致：查询、更新、删除 200，创建 201 |
| data | object / array | 记录、分页对象或删除后的空数组 `[]` |
| meta.timestamp | number | Unix 秒时间戳，可带小数 |
| meta.response_time | number | 从请求开始到生成响应的耗时，单位秒 |

时间字段通常为 UTC ISO 8601（如 `2026-11-10T00:00:00.000000Z`）；未进行日期转换的数据库字段可能返回数据库日期字符串。套餐入参建议使用带时区的 ISO 8601。

## 错误响应

| HTTP/code | 场景 |
| --- | --- |
| 401 | 认证请求头无效或过期、应用凭证无效、签名无效 |
| 403 | IP 不在白名单内 |
| 404 | 记录不存在、已删除或关联记录不属于路径中的企业 |
| 409 | nonce 重复，或主记录仍有外键关联无法删除 |
| 422 | 必填、类型、长度、唯一性、外键或业务校验失败 |
| 429 | 超出路由限流，每个限流键每分钟 120 次；当前未使用 APP_KEY 作为独立限流键 |
| 500 | 未预期服务端错误 |

```json
{
  "code": 401,
  "message": "签名无效"
}
```

参数校验失败示例（debug 关闭时不返回 errors）：

```json
{
  "code": 422,
  "message": "密码不能少于8个字符",
  "errors": {
    "password": ["密码不能少于8个字符"]
  }
}
```

错误响应没有 data 和 meta。`message` 为说明文字，多个字段失败时可能包含汇总后缀；业务应结合 HTTP 状态码处理。

## 分页与更新约定

所有列表按 ID 倒序，参数 `per_page` 可选整数 1–100（默认 20）；`page` 由分页器处理，建议正整数，默认 1，当前不属于 FormRequest 显式校验字段。暂未提供按姓名、手机号、企业或状态过滤。

| data 字段 | 类型 | 说明 |
| --- | --- | --- |
| data | array | 本页记录列表，空页为 `[]` |
| current_page / last_page | integer | 当前页 / 最后一页 |
| per_page / total | integer | 每页数量 / 总记录数 |
| from / to | integer 或 null | 当前页起止记录序号，空页为 null |
| first_page_url / last_page_url / path | string | 分页地址及资源地址 |
| next_page_url / prev_page_url | string 或 null | 相邻页地址，无相邻页时为 null |
| links | array | 分页导航，包含 url、label、active；框架版本可能附加其他字段 |

目前分页 URL 不一定保留 per_page，客户端翻页应自行保留分页大小并为实际 URI 重新签名。
PUT 与 PATCH 都仅更新提交字段，不进行整条替换；nullable 字段可用 null 清空，必填字段提交后仍不能为空。未列为可写入参的字段不会保存。
创建响应直接序列化新模型时可能不包含数据库补入的默认值或未提交的空字段，详情/更新响应读取数据库后的记录更完整。
