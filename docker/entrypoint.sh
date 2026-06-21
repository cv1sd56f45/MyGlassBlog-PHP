#!/bin/bash
set -e

# 容器启动脚本：自动从环境变量生成 config.php

CONFIG_FILE="/var/www/html/config.php"

# 如果 config.php 不存在，则根据环境变量创建
if [ ! -f "$CONFIG_FILE" ] || [ -f "/var/www/html/config.lock" ]; then
    # 默认值
    DB_HOST="${DB_HOST:-localhost}"
    DB_PORT="${DB_PORT:-3306}"
    DB_NAME="${DB_NAME:-myglassblog}"
    DB_USER="${DB_USER:-root}"
    DB_PASS="${DB_PASS:-}"
    DB_CHARSET="${DB_CHARSET:-utf8mb4}"
    SITE_URL="${SITE_URL:-}"
    DEBUG="${DEBUG:-false}"
    POSTS_PER_PAGE="${POSTS_PER_PAGE:-10}"
    CHATTERS_PER_PAGE="${CHATTERS_PER_PAGE:-20}"

    cat > "$CONFIG_FILE" <<EOF
<?php
return [
    'db_host' => '$DB_HOST',
    'db_port' => $DB_PORT,
    'db_name' => '$DB_NAME',
    'db_user' => '$DB_USER',
    'db_pass' => '$DB_PASS',
    'db_charset' => '$DB_CHARSET',
    'site_url' => '$SITE_URL',
    'upload_dir' => 'uploads',
    'debug' => $DEBUG,
    'posts_per_page' => $POSTS_PER_PAGE,
    'chatters_per_page' => $CHATTERS_PER_PAGE,
];
EOF

    chown www-data:www-data "$CONFIG_FILE"
    chmod 664 "$CONFIG_FILE"
fi

# 确保 uploads 目录存在且有权限
mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads
chmod -R 775 /var/www/html/uploads

# 执行 Apache 前台进程
exec apache2-foreground
