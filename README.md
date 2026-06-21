# MyGlassBlog PHP ✨

一个优雅的毛玻璃风格个人博客系统，PHP + MySQL 构建，宝塔面板友好部署。

![PHP](https://img.shields.io/badge/PHP-7.4+-777bb4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479a1?style=flat-square&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

---

## ✨ 特性

- **毛玻璃设计** — Glassmorphism 风格，优雅现代
- **暗色模式** — 支持亮/暗主题切换
- **Markdown 写作** — 支持 Markdown 语法、代码高亮
- **说说功能** — 随时记录碎片化想法
- **照片墙** — 展示精彩瞬间
- **友链系统** — 收录志同道合的朋友
- **时间线** — 记录人生里程碑
- **评论系统** — 支持文章评论
- **后台管理** — 浏览器内直接管理内容
- **宝塔友好** — 无需命令行，图形化部署

---

## 🐳 Docker 镜像

GitHub Actions 自动构建并推送镜像到 GitHub Container Registry：

```bash
# 拉取最新镜像
docker pull ghcr.io/cv1sd56f45/MyGlassBlog-PHP:latest

# 运行
docker run -d -p 3000:80 \
  -e DB_HOST=your-db-host \
  -e DB_NAME=your-db-name \
  -e DB_USER=your-db-user \
  -e DB_PASS=your-db-pass \
  ghcr.io/cv1sd56f45/MyGlassBlog-PHP:latest
```

每次推送到 `main` 分支或发布 `v*` 标签都会自动构建镜像。

---

## 🚀 部署方式

### 方式一：Docker 部署（推荐）

支持 Linux / macOS / Windows，一条命令启动。

```bash
# 克隆仓库
git clone https://github.com/cv1sd56f45/MyGlassBlog-PHP.git
cd MyGlassBlog-PHP

# 启动容器（含 PHP + MySQL）
docker compose up -d

# 访问
# 前台：http://localhost:3000
# 后台：http://localhost:3000/admin/
# 默认账号：admin / admin123
```

Docker 环境变量：

| 变量 | 默认值 | 说明 |
|------|--------|------|
| `DB_HOST` | localhost | 数据库主机 |
| `DB_PORT` | 3306 | 数据库端口 |
| `DB_NAME` | myglassblog | 数据库名 |
| `DB_USER` | root | 用户名 |
| `DB_PASS` | 空 | 密码 |

### 方式二：宝塔面板部署

### 环境要求

- 宝塔面板 7.x+
- PHP 7.4+（推荐 8.x）
- MySQL 5.7+
- Nginx / Apache

### 部署步骤

#### 1. 创建网站

在宝塔面板 → 网站 → 添加站点：

| 项目 | 值 |
|------|-----|
| 域名 | 你的域名（如 `blog.example.com`） |
| 根目录 | `/www/wwwroot/blog.example.com` |
| PHP版本 | PHP 8.x |
| 数据库 | MySQL（选择或创建） |

#### 2. 上传代码

**方法一：Git 克隆（推荐）**

在宝塔终端执行：
```bash
cd /www/wwwroot/blog.example.com
git clone https://github.com/cv1sd56f45/MyGlassBlog-PHP.git .
```

**方法二：上传 ZIP**

1. 下载 ZIP 包
2. 在宝塔文件管理器中上传并解压

#### 3. 设置目录权限

在宝塔文件管理器中，右键网站根目录 → 权限 → 设置为 `755`，所有者 `www`

需要可写的目录：
- `uploads/`（自动创建）
- `config.php`

#### 4. 运行安装向导

浏览器访问：`https://你的域名/install.php`

按向导提示完成安装：
1. 环境检测
2. 填写数据库信息（在宝塔数据库管理中查看）
3. 设置管理员账号

#### 5. 完成

- 前台：`https://你的域名/`
- 后台：`https://你的域名/admin/`

---

## 📂 目录结构

```
MyGlassBlog-PHP/
├── admin/                  # 后台管理
│   ├── login.php           # 登录页
│   ├── index.php           # 仪表盘
│   ├── posts.php           # 文章管理
│   ├── chatters.php        # 说说管理
│   ├── photos.php          # 照片管理
│   ├── friends.php         # 友链管理
│   ├── timeline.php        # 时间线管理
│   ├── comments.php        # 评论管理
│   └── settings.php        # 站点设置
├── includes/               # 核心类库
│   ├── Database.php        # 数据库操作
│   ├── Post.php            # 文章模型
│   ├── Chatter.php         # 说说模型
│   ├── Photo.php           # 照片模型
│   ├── Friend.php          # 友链模型
│   ├── Timeline.php        # 时间线模型
│   ├── Comment.php         # 评论模型
│   ├── Settings.php        # 配置管理
│   └── functions.php       # 工具函数
├── templates/              # 模板文件
│   ├── header.php          # 头部
│   └── footer.php          # 底部
├── uploads/                # 上传目录
├── index.php               # 首页
├── posts.php               # 文章列表
├── post.php                # 文章详情
├── chatter.php             # 说说页
├── photowall.php           # 照片墙
├── friends.php             # 友链页
├── timeline.php            # 时间线
├── about.php               # 关于页
├── install.php             # 安装向导
├── config.php              # 配置文件（安装时生成）
├── database.sql            # 数据库结构
└── README.md               # 说明文档
```

---

## ⚙️ 配置说明

### 站点设置

登录后台 → 站点设置，可修改：
- 网站标题、作者名、个人简介
- 头像、社交链接
- 主题颜色（建议2-4个颜色）
- 分页数量

### 主题颜色示例

```
紫色系：#a18cd1,#fbc2eb
蓝色系：#2193b0,#6dd5ed
绿色系：#11998e,#38ef7d
粉色系：#ee9ca7,#ffdde1
```

---

## 🛠️ 技术栈

| 技术 | 说明 |
|------|------|
| PHP 7.4+ | 后端语言 |
| MySQL 5.7+ | 数据库 |
| Tailwind CSS | 样式框架（CDN） |
| Vanilla JS | 原生 JavaScript |

---

## 📝 更新日志

### v1.0.0 (2026-06-21)
- 首次发布
- 完整的博客功能
- 宝塔面板友好部署
- 毛玻璃设计风格

---

## 🙏 致谢

本项目由 **QClaw (OpenClaw)** 协助开发完成。

---

## 📄 License

MIT License © 2026 cv1sd56f45

---

*如果这个项目对你有帮助，欢迎 Star ⭐*
