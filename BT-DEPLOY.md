# 宝塔面板部署指南

本文档详细介绍如何在宝塔面板部署 MyGlassBlog PHP。

---

## 前置条件

- 已安装宝塔面板（推荐 7.x 或更高版本）
- 已安装 PHP 7.4+（推荐 8.x）
- 已安装 MySQL 5.7+
- 已安装 Nginx 或 Apache

---

## 第一步：创建网站

1. 登录宝塔面板
2. 点击左侧菜单 **「网站」**
3. 点击 **「添加站点」**
4. 填写信息：
   - **域名**：你的域名（如 `blog.example.com`）
   - **根目录**：`/www/wwwroot/你的域名`
   - **PHP版本**：选择 PHP 8.x
   - **数据库**：选择「创建数据库」或稍后手动创建
5. 点击 **「提交」**

---

## 第二步：上传代码

### 方法一：下载发布版（推荐）

1. 访问 [GitHub Releases](https://github.com/cv1sd56f45/MyGlassBlog-PHP/releases)
2. 下载最新版本的 `MyGlassBlog-PHP-vX.X.X.zip`
3. 宝塔面板 → 文件 → 进入网站根目录
4. 上传 zip 文件并解压

### 方法二：Git Clone

宝塔终端执行：
```bash
cd /www/wwwroot/你的域名
git clone https://github.com/cv1sd56f45/MyGlassBlog-PHP.git .
```

---

## 第三步：创建数据库

1. 宝塔面板 → 数据库 → 添加数据库
2. 填写信息：
   - **数据库名**：`myglassblog`（或自定义）
   - **用户名**：`myglassblog`（或自定义）
   - **密码**：自动生成或自定义
3. 点击 **「提交」**
4. 记录数据库名、用户名、密码，后续安装需要

---

## 第四步：导入数据库结构

1. 宝塔面板 → 数据库 → 点击数据库名后的 **「管理」**
2. 进入 phpMyAdmin
3. 点击 **「导入」**
4. 选择项目根目录下的 `database.sql` 文件
5. 点击 **「执行」**

---

## 第五步：设置目录权限

宝塔终端执行：
```bash
cd /www/wwwroot/你的域名
chmod -R 755 .
chmod -R 777 uploads/
chown -R www:www .
```

或在宝塔文件管理器中：
1. 右键 `uploads/` 目录 → 权限 → 设为 777
2. 勾选「应用到子目录」

---

## 第六步：运行安装向导

1. 浏览器访问 `http://你的域名/install.php`
2. 按向导填写：
   - **数据库主机**：`localhost`
   - **数据库端口**：`3306`
   - **数据库名**：刚才创建的数据库名
   - **数据库用户**：刚才创建的用户名
   - **数据库密码**：刚才的密码
   - **管理员用户名**：自定义（如 `admin`）
   - **管理员密码**：自定义
3. 点击 **「开始安装」**
4. 安装成功后会自动跳转到首页

---

## 第七步：访问后台

安装完成后：

- **前台首页**：`http://你的域名/`
- **后台管理**：`http://你的域名/admin/`
- 登录账号：安装时设置的管理员用户名和密码

---

## 常见问题

### 1. install.php 报错 "config.php 不可写"

手动创建配置文件：
```bash
touch /www/wwwroot/你的域名/includes/config.php
chmod 777 /www/wwwroot/你的域名/includes/config.php
```

### 2. 页面显示空白或 500 错误

检查 PHP 错误日志：
- 宝塔面板 → 网站 → 你的站点 → 日志 → PHP 错误日志

常见原因：
- PHP 扩展未安装（PDO、mysqli）
- 目录权限问题
- 数据库连接失败

### 3. Apache 下伪静态不生效

确保 `.htaccess` 文件存在，且 Apache 已启用 `mod_rewrite`。

宝塔面板 → 网站 → 你的站点 → 设置 → 伪静态 → 选择「当前」或手动添加：
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?/$1 [L]
```

### 4. Nginx 下伪静态配置

宝塔面板 → 网站 → 你的站点 → 设置 → 伪静态 → 添加：
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/tmp/php-cgi-82.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

---

## 安全建议

1. **删除安装文件**：安装完成后删除或重命名 `install.php`
   ```bash
   rm /www/wwwroot/你的域名/install.php
   ```
2. **修改后台路径**：将 `admin/` 目录重命名为不易猜到的名称
3. **定期备份**：宝塔面板 → 计划任务 → 添加数据库和网站备份
4. **启用 SSL**：宝塔面板 → 网站 → 你的站点 → SSL → Let's Encrypt 免费证书

---

## 更新升级

1. 备份数据库和 `config.php` 文件
2. 下载最新版本覆盖文件（保留 `config.php` 和 `uploads/`）
3. 如有数据库变更，执行升级 SQL 脚本

---

## 技术支持

- GitHub Issues: https://github.com/cv1sd56f45/MyGlassBlog-PHP/issues
- GitHub Releases: https://github.com/cv1sd56f45/MyGlassBlog-PHP/releases
