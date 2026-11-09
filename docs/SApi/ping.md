# Ping 连通性检测

`GET /sapi/ping`

需应用签名鉴权，参见[接入说明](authentication.md)。无查询参数、路径参数及请求体。用于检查路由可达及凭证有效性。

请求示例：

```bash
php sapi-client.php GET /sapi/ping
```

HTTP 200 响应：

```json
{
  "code": 200,
  "data": {
    "message": "pong"
  },
  "meta": {
    "timestamp": 1794268800.123,
    "response_time": 0.012
  }
}
```

`data.message` 固定为字符串 `pong`；code、meta 见公共约定。认证失败返回 401，IP 不允许返回 403，请求重放返回 409，限流返回 429。
