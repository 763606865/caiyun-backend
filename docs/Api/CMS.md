# CMS 使用说明

## 初始化

```bash
php artisan cms:install
```

命令会执行数据库迁移、权限初始化、创建默认栏目和站点配置，并引导创建超级管理员。无交互部署可使用：

```bash
php artisan cms:install --site-name="我的站点" --no-admin
php artisan admin:create --name="Admin" --email="admin@example.com" --password="change-me-now"
```

## 后台功能

- 内容：文章/单页/外链、草稿、待发布、已发布、下线、定时上下线、预览、回收站、SEO。
- 分类：多级栏目、标签、排序和启停。
- 媒体：统一文件记录、分组、标题和替代文本。
- 治理：细粒度权限、内容修订快照、管理操作日志。

请确保生产环境的 Laravel Scheduler 每分钟运行，定时发布和下线依赖该任务。

## 公开 API

- `GET /api/cms/categories`：栏目树。
- `GET /api/cms/contents`：已发布内容，支持 `category`、`tag`、`q`、`featured`、`per_page`。
- `GET /api/cms/contents/{slug}`：内容详情。
- `GET /sitemap.xml`：已发布内容 Sitemap。

公开查询会同时检查发布状态、发布时间和下线时间。未发布内容只能通过后台生成的限时签名链接预览。
