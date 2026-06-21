#!/usr/bin/env bash
set -e

# MyGlassBlog-PHP 宝塔面板一键部署脚本
# 用法：将本脚本上传到宝塔终端，执行 bash bt-deploy.sh

PROJECT_DIR="/www/wwwroot/MyGlassBlog-PHP"
REPO_URL="https://github.com/cv1sd56f45/MyGlassBlog-PHP.git"

echo "=== MyGlassBlog-PHP 宝塔部署脚本 ==="

# 1. 克隆项目
if [ -d "$PROJECT_DIR" ]; then
    echo "目录已存在，更新代码..."
    cd "$PROJECT_DIR"
    git pull origin master
else
    echo "正在克隆项目到 $PROJECT_DIR ..."
    git clone "$REPO_URL" "$PROJECT_DIR"
    cd "$PROJECT_DIR"
fi

# 2. 设置目录权限
chown -R www:www "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/uploads"
chmod 664 "$PROJECT_DIR/config.php"

# 3. 导入数据库（如果数据库不存在）
DB_NAME="myglassblog"
DB_USER="myglassblog"
DB_PASS="myglassblog123"

echo "请先在宝塔面板创建数据库：$DB_NAME，并执行 database.sql 导入表结构"

# 4. 提示完成
echo ""
echo "=== 部署完成 ==="
echo "前台地址：http://你的域名/"
echo "后台地址：http://你的域名/admin/"
echo "默认账号：admin / admin123"
echo ""
echo "如需 Docker 部署，请执行：docker compose up -d"
