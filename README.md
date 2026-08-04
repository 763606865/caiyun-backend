# 项目准备

## 环境要求
- Node.js >= 25.8
- npm >= 11.11
- pnpm >= 10.33
- PHP >= 8.5
- Composer >= 2.9
- MySQL >= 9.6
- Redis >= 8.6
- Laravel >= 13.3
- Supervisor >= 4.2
- ElasticSearch >= 9.0

## 快速初始化
### 1) 配置 Laravel 目录权限

Laravel 运行时需要 Web/PHP 进程能够写入 `bootstrap/cache` 和 `storage`。在 Linux 服务器上，先将目录所属组改为 PHP-FPM/Web 服务使用的用户组（Debian/Ubuntu 通常为 `www-data`，CentOS/RHEL 可能为 `nginx` 或 `apache`）：

```bash
sudo chown -R "$(whoami)":www-data bootstrap/cache storage
sudo find bootstrap/cache storage -type d -exec chmod 775 {} \;
sudo find bootstrap/cache storage -type f -exec chmod 664 {} \;
```

本地开发时，如果 Composer、PHP 和 Web 服务均以当前用户运行，通常只需执行：

```bash
chmod -R u+rwX bootstrap/cache storage
```

> 不要直接对整个项目执行 `chmod -R 777`。生产环境应让部署用户拥有文件、PHP-FPM/Web 服务用户组拥有组写权限；如果服务器使用 ACL、SELinux 或容器挂载卷，还需同步配置对应的写权限。

若目录不存在（例如部署流程未保留 Git 中的空目录），可先创建 Laravel 常用运行时目录：

```bash
mkdir -p bootstrap/cache \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs
```

权限配置完成后，可验证当前用户是否能够写入：

```bash
test -w bootstrap/cache && test -w storage && echo "Laravel 目录可写"
```

### 2) 安装后端依赖
```bash
composer install
```

### 3) 生成应用密钥
```bash
php artisan key:generate
```

### 4) 执行数据库迁移
```bash
php artisan migrate
```

### 5) 填充基础数据
```bash
php artisan db:seed
```

## Docker Compose 部署

### 1) 准备环境变量
```bash
cp .env.docker.example .env.docker
```

编辑 `.env.docker`，至少补齐：
- `APP_KEY`：可先用 `docker compose --env-file .env.docker run --rm app php artisan key:generate --show` 生成后写入
- `APP_URL`：服务器对外访问地址
- `DB_PASSWORD`：MySQL root 密码
- `ELASTIC_PASSWORD`：ElasticSearch `elastic` 用户密码
- OSS、短信、IM、AI 等第三方服务配置

### 2) 构建并启动服务
```bash
docker compose --env-file .env.docker up -d --build
```

服务包含：
- `nginx`：Web 入口，默认映射 `${APP_PORT:-80}`
- `app`：Laravel PHP-FPM
- `horizon`：队列消费
- `scheduler`：Laravel 定时任务
- `mysql`：业务数据库
- `redis`：缓存、Session、队列
- `elasticsearch`：Scout 搜索服务

### 3) 初始化应用
```bash
docker compose --env-file .env.docker exec app php artisan migrate --force
docker compose --env-file .env.docker exec app php artisan passport:keys --force
docker compose --env-file .env.docker exec app php artisan storage:link
docker compose --env-file .env.docker exec app php artisan optimize
```

如需初始化搜索索引：
```bash
docker compose --env-file .env.docker exec app php artisan scout:index "App\Models\Rc\Resume"
docker compose --env-file .env.docker exec app php artisan scout:index "App\Models\Rc\Job"
docker compose --env-file .env.docker exec app php artisan scout:import "App\Models\Rc\Resume"
docker compose --env-file .env.docker exec app php artisan scout:import "App\Models\Rc\Job"
```

### 4) 常用运维命令
```bash
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker logs -f app
docker compose --env-file .env.docker logs -f horizon
docker compose --env-file .env.docker restart app horizon scheduler
```

## 管理员账号说明
> `php artisan make:filament-user` 已弃用（本项目不建议使用）。

该命令默认通过 `Hash::make` 注入密码，在当前项目中可能导致无法登录。

建议直接写入数据库创建管理员，并使用 `bcrypt` 进行密码加密。

## 生成 Passport 客户端
```bash
php artisan passport:client --name="牛派B端" --provider=b_users --personal
```

## 生成 Rc 客户端
```bash
php artisan passport:client --name="招聘C端" --provider=rc_users --personal
```

## 前端（中台）
### 安装依赖
```bash
pnpm install
```

### 启动filament
```bash
pnpm build
```

## 队列（Horizon 模式）
### 1) 安装 Horizon（仅首次）
```bash
php artisan horizon:install
php artisan migrate
```

### 2) Supervisor 配置更新后刷新
```bash
supervisorctl reread
supervisorctl update
supervisorctl restart newpr-backend-horizon:
supervisorctl status
```

### 3) 状态检查
```bash
php artisan horizon:status
```

> 已使用 Horizon 时，不建议再并行运行 `queue:work` 的常驻进程，避免消费链路混用。

### 定时任务配置
```bash
crontab -e
```

```bash
* * * * * cd /Users/zn/workspace/code/newpr_backend && php artisan schedule:run >> /dev/null 2>&1
```

### ElasticSearch 数据初始化索引
```bash
# 1. 创建索引
php artisan scout:index "App\Models\Rc\Resume"
php artisan scout:index "App\Models\Rc\Job"

# 2. 全量导入数据
php artisan scout:import "App\Models\Rc\Resume"
php artisan scout:import "App\Models\Rc\Job"

# 3. 更新索引
php artisan scout:flush "App\Models\Rc\Resume"
php artisan scout:flush "App\Models\Rc\Job"
```

### 生成Filament模块权限
```bash
# 1.全量
php artisan shield:generate --all --panel=admin --no-interaction
# 2.单个资源
php artisan shield:generate --resource=YourResource --panel=admin --no-interaction
# 3.生成超级管理员权限
php artisan shield:super-admin --user=1 --panel=admin
```
