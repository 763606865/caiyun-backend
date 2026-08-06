# OpenAPI 文档

OpenAPI 面向甲方及合作方的服务端应用，不适合在浏览器、App、小程序等无法安全保管 `Client Secret` 的环境中直接调用。

## 接入步骤

1. 运营人员在管理后台“系统设置 → OpenAPI 密钥”创建调用方。
2. 通过安全渠道交付 `Client ID` 和 `Client Secret`。
3. 调用方按照[认证、加密与验签协议](认证、加密与验签.md)构造请求。
4. 按具体接口文档处理业务响应。

## 接口

- [授权校验](授权校验.md)：`POST /openapi/v1/leases`

所有接口必须使用 HTTPS。`Client Secret` 泄露后应立即在后台轮换，旧密钥会即时失效。
