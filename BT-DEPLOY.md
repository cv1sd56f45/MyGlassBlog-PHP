# 宝塔面板部署指南

## 快速部署（5分钟完成）

### 第一步：创建网站

1. 登录宝塔面板
2. 点击左侧「网站」→「添加站点」
3. 填写信息：
   - 域名：`blog.example.com`（你的域名）
   - 根目录：`/www/wwwroot/blog.example.com`
   - PHP版本：PHP 8.0 或以上
   - 数据库：选择「创建数据库」，记下数据库名、用户名、密码

### 第二步：上传代码

**方法一：Git 克隆（推荐）**

点击宝塔左侧「终端」，执行：
```bash
cd /www/wwwroot/blog.example.com
git clone https://github.com/cv1sd56f45/MyGlassBlog-PHP.git .
chown -R www:www .
chmod -R 755 .
```

**方法二：上传文件**

1. 下载 ZIP 包
2. 点击宝塔左侧「文件」
3. 进入网站目录 `/www/wwwroot/blog.example.com`
4. 点击「上传」，选择 ZIP 文件
5. 右键 ZIP 文件 → 解压

### 第三步：设置权限

在文件管理器中：
1. 右键网站根目录
2. 选择「权限」
3. 设置为 `755`，勾选「应用到子目录」
4. 所有者选择 `www`

### 第四步：运行安装向导

浏览器访问：`http://你的域名/install.php`

1. **环境检测** → 确认全部通过，点「下一步」
2. **数据库配置** → 填写第一步创建的数据库信息
3. **管理员设置** → 设置用户名和密码
4. **完成** → 点击访问首页或进入后台

---

## 常见问题

### 1. 访问 install.php 显示 500 错误

检查 PHP 版本是否 7.4+，在「软件商店」→「PHP」中切换版本。

### 2. 数据库连接失败

- 检查数据库用户名和密码是否正确
- 在宝塔「数据库」中重置密码后再试

### 3. 安装后访问首页空白

检查 `config.php` 文件是否存在，权限是否正确。

### 4. 后台登录不了

默认账号：`admin` / `admin123`

如果自己修改过密码忘了，在宝塔「数据库」→「管理」→ 打开 phpMyAdmin：

```sql
-- 重置密码为 admin123
UPDATE admins SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE id = 1;
```

### 5. 图片上传不了

检查 `uploads/` 目录是否存在，权限是否 755，所有者是否 `www`。

### 6. 想修改网站设置

登录后台 → 站点设置，在网页中修改即可。

---

## 安全建议

1. **删除安装文件**：安装完成后 `install.php` 会自动删除，如未删除请手动删除
2. **修改后台密码**：使用复杂密码
3. **开启 HTTPS**：在宝塔「网站」→「SSL」中申请免费证书
4. **定期备份**：在宝塔「计划任务」中设置自动备份

---

## 更新版本

```bash
cd /www/wwwroot/blog.example.com
git pull origin main
```

如有数据库变更，执行对应的 SQL 更新脚本。

---

## 联系支持

- 提 Issue：https://github.com/cv1sd56f45/MyGlassBlog-PHP/issues
