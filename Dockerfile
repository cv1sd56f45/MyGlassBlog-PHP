FROM php:8.2-apache

# 安装系统依赖和 PHP 扩展
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip mysqli \
    && rm -rf /var/lib/apt/lists/*

# 启用 Apache rewrite 模块
RUN a2enmod rewrite

# 复制自定义 Apache 配置
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# 设置工作目录
WORKDIR /var/www/html

# 先复制 composer 文件（如果有）以便利用缓存
COPY composer.json composer.lock* /var/www/html/

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 安装 PHP 依赖（如果 composer.json 存在）
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# 复制项目代码
COPY . /var/www/html/

# 设置目录权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads \
    && chmod 664 /var/www/html/config.php

# 健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

# 复制入口脚本
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
