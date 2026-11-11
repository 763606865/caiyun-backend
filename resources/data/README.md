# 省市区字典

`china-regions.json` 用于 Admin 省、市、区县三级联动，随项目分发，不在页面运行时请求外部服务。

- 来源：[modood/Administrative-divisions-of-China](https://github.com/modood/Administrative-divisions-of-China)
- 固定版本：`dc3a1d7acd85ca1b0979543e9259604142c52e8e`
- 原始文件：[dist/pca-code.json](https://github.com/modood/Administrative-divisions-of-China/blob/dc3a1d7acd85ca1b0979543e9259604142c52e8e/dist/pca-code.json)
- 原始文件 SHA-256：`83b7536f853ad16beb4d37b92890a3fd7bb9d33d4f37e7c8885fb948749a9bc4`
- 数据为 2023 年快照，覆盖大陆 31 个省级地区，不包含港澳台。上游已停止更新，不能视为实时行政区划。
- 授权：WTFPL，原文见 `china-regions.LICENSE`。

转换规则：省级二位码和地级四位码右补零为六位码；区县只取六位码，排除乡镇、街道的九位码；按 `provinces`、`cities`、`districts` 建立索引。

对于没有区县选项的城市，区县下拉框禁用，保存城市编码。字段为可选项：仅选择省或市时保存该层级编码；清空省份则清空地区码。

更新数据时应替换完整快照，保留来源与版本记录，并验证地区父子关系及既有组织的编辑回显。
