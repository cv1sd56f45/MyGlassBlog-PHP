# MyGlassBlog PHP ✨

一个优雅的毛玻璃风格个人博客系统，PHP + MySQL 构建，支持宝塔面板和传统服务器部署。

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

## 🚀 部署方式

### 方式一：宝塔面板部署（推荐）

已提供宝塔一键部署脚本 [`bt-deploy.sh`](bt-deploy.sh) 和详细图文指南 [`BT-DEPLOY.md`](BT-DEPLOY.md)。

宝塔终端执行：
```bash
bash /www/wwwroot/MyGlassBlog-PHP/bt-deploy.sh
```

### 方式二：传统安装（任意服务器）

#### 环境要求

- PHP 7.4+（推荐 8.x）
- MySQL 5.7+
- Nginx / Apache
- 已启用 `mod_rewrite`（Apache）或配置 `try_files`（Nginx）

#### 安装步骤

1. 上传代码到网站根目录
2. 创建 MySQL 数据库并导入 `database.sql`
3. 设置目录权限：
   - `uploads/` 可写
   - `config.php` 可写
4. 浏览器访问 `http://你的域名/install.php`
5. 按向导填写数据库信息并设置管理员账号
6. 完成：
   - 前台：`http://你的域名/`
   - 后台：`http://你的域名/admin/`

---

## 📂 目录结构

```
MyGlassBlog-PHP/
├── admin/                    # 后台管理
│   ├── login.php             # 登录页
│   ├── index.php             # 仪表盘
│   ├── posts.php             # 文章管理
│   ├── chatters.php          # 说说管理
│   ├── photos.php            # 照片管理
│   ├── friends.php           # 友链管理
│   ├── timeline.php          # 时间线管理
│   ├── comments.php          # 评论管理
│   └── settings.php          # 站点设置
├── includes/                 # 核心类库
│   ├── Database.php          # 数据库操作
│   ├── Post.php              # 文章模型
│   ├── Chatter.php           # 说说模型
│   ├── Photo.php             # 照片模型
│   ├── Friend.php            # 友链模型
│   ├── Timeline.php          # 时间线模型
│   ├── Comment.php           # 评论模型
│   ├── Settings.php          # 配置管理
│   └── functions.php         # 工具函数
├── templates/                # 模板文件
│   ├── header.php            # 头部
│   └── footer.php            # 底部
├── uploads/                  # 上传目录
├── bt-deploy.sh              # 宝塔一键部署脚本
├── BT-DEPLOY.md              # 宝塔部署图文指南
├── index.php                 # 首页
├── posts.php                 # 文章列表
├── post.php                  # 文章详情
├── chatter.php               # 说说页
├── photowall.php             # 照片墙
├── friends.php               # 友链页
├── timeline.php              # 时间线
├── about.php                 # 关于页
├── install.php               # 安装向导
├── config.php                # 配置文件（安装时生成）
├── database.sql              # 数据库结构
├── .htaccess                 # Apache URL 重写
└── README.md                 # 说明文档
```

---

## ⚙️ 配置说明

### 站点设置

登录后台 → 站点设置，可修改：
- 网站标题、作者名、个人简介
- 头像、社交链接
- 主题颜色（建议 2-4 个颜色）
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

### v1.0.1 (2026-06-21)
- 修复 install.php 表单 action 属性缺失问题
- 补全后台管理页面（photos/friends/timeline/comments）

### v1.0.0 (2026-06-21)
- 首次发布
- 完整的博客功能（文章、说说、照片墙、友链、时间线、评论）
- 后台管理系统
- 宝塔面板一键部署脚本
- 毛玻璃设计风格

---

## 🙏 致谢

本项目由 **QClaw (OpenClaw)** 协助开发完成。

---

## 📄 License

MIT License © 2026 cv1sd56f45

---

*如果这个项目对你有帮助，欢迎 Star ⭐*
