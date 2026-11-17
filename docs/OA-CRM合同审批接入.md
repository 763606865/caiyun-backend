# OA / CRM 合同审批交换接入

日期：2026-11-17。本仓库只负责身份授权、持久交换和撤回仲裁。CRM 维护业务合同，OA 维护审批决定。

## 部署

执行新增迁移 `2026_11_16_000001_create_platform_contract_approvals.php`。企业、申请人的任职身份及 OA/CRM 企业/人员授权使用第一阶段已有配置。

通过 `php artisan platform:integration <应用code> --enterprise=<企业UUID> --scope=<scope>` 配置企业级 scope，可重复传 `--scope`；OA 新增 `auth.delegate` 和 `contracts.receive`，CRM 新增 `contracts.submit`。保留原有 auth/目录 scopes。不要分配平台全局运维权限。

在应用 redirect_uris 中精确登记前端 `/integration/caiyun/callback` 完整地址。所有 SAPI 使用原有 HMAC、nonce、时间窗校验。

## 接口

- `POST /sapi/auth/delegated-tickets`：有 auth.delegate 的 OA 服务端提交由已登录成员派生的企业/用户/任职 UUID、目标 application_code、redirect_uri、state。两端授权及白名单均通过后签发 60 秒单次 code，原有 `/sapi/auth/exchange` 消费。
- `POST /sapi/integration/contract-approvals`：来源 contracts.submit；uuid、enterprise_uuid、contract_uuid、revision、target_application_code、applicant_user_uuid、applicant_employment_uuid、snapshot_json、snapshot_hash，可选 cancel_requested。
- `GET /sapi/integration/contract-approvals?enterprise_uuid=...&after_id=...`：接收方 contracts.receive，分页100，返回 items/next_id/has_more，仅 queued/cancelling。
- `GET /sapi/integration/contract-approvals/{uuid}?enterprise_uuid=...`：仅来源/接收方、同企业。
- `POST /sapi/integration/contract-approvals/{uuid}/result`：接收方提交 enterprise_uuid、snapshot_hash、pending/approved/rejected/cancelled、oa_contract_uuid、oa_instance_uuid、comment；取消前尚未建 OA 对象时允许关联为空。
- `POST /sapi/integration/contract-approvals/{uuid}/cancel`：来源提交 enterprise_uuid，排队撤回或返回已存在终态。

snapshot_json 是提交时合同编号、名称、客户名称、金额、币种、条款和日期的原始 JSON 字符串；snapshot_hash 是该原始字符串的 SHA-256。相同 uuid 不得替换身份、合同、版本、摘要或接收应用；同合同同版本也不能重复创建请求。

queued → pending → approved/rejected；queued/pending → cancelling → cancelled。若 OA 决定已提交但回传晚于撤回，允许回传该实际终态。已结束状态不能被覆盖。未送达撤回可在申请人撤权后建立取消记录，但不能创建新的待审批业务。

## 验证

`php vendor/bin/pint --test`；`php vendor/bin/phpstan analyse --memory-limit=2G`；`php -d memory_limit=1G vendor/bin/phpunit`。

最终完整套件 107 项、754 断言通过；覆盖企业/应用边界、快照保护、终态、撤回竞态、撤权取消补偿和委托票据单次消费。本机真实 HTTP/HMAC、OA 浏览器人工通过、CRM 回写与交接记录详见配套 CRM 网页仓库 `docs/OA与CRM闭环实施与验收-2026-11-17.md`。
