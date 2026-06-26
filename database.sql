-- ============================================
-- MyGlassBlog PHP 版本数据库结构
-- 适用于宝塔面板 + MySQL 5.7+
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 1. 管理员表
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码（bcrypt加密）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 默认管理员：admin / admin123
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ----------------------------
-- 2. 文章表
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `slug` varchar(200) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `content` longtext COMMENT 'Markdown内容',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '摘要',
  `cover` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图URL',
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '阅读量',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0草稿 1发布',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- 示例文章
INSERT INTO `posts` (`title`, `slug`, `content`, `category`, `description`, `cover`, `status`) VALUES
('你好，世界', 'hello-world', '# 你好，世界！\n\n这是我的第一篇博客文章。\n\n## 欢迎来到我的博客\n\n这个博客使用 **PHP + MySQL** 构建，采用毛玻璃设计风格。\n\n```php\necho \"Hello, World!\";\n```\n\n感谢你的访问！', '随笔', '这是我的第一篇博客文章，欢迎访问！', '', 1),
('博客搭建记录', 'dfew', '# 博客搭建记录\n\n今天把博客从 Next.js 迁移到了 PHP，宝塔部署更方便了。\n\n## 为什么迁移\n\n- 宝塔面板原生支持 PHP\n- 不需要 Node.js 运行时\n- 数据库管理更直观\n- 后台功能更好维护', '技术', '博客从 Next.js 迁移到 PHP 的记录', '', 1);

-- ----------------------------
-- 3. 说说表
-- ----------------------------
DROP TABLE IF EXISTS `chatters`;
CREATE TABLE `chatters` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '内容',
  `images` varchar(2000) NOT NULL DEFAULT '' COMMENT '图片URL（JSON数组）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='说说表';

-- 示例说说
INSERT INTO `chatters` (`content`) VALUES
('今天天气真好，适合写代码 🚀'),
('博客终于上线了，开心！');

-- ----------------------------
-- 4. 照片墙表
-- ----------------------------
DROP TABLE IF EXISTS `photos`;
CREATE TABLE `photos` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '描述',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '图片URL',
  `thumb` varchar(500) NOT NULL DEFAULT '' COMMENT '缩略图URL',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0隐藏 1显示',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='照片墙表';

-- ----------------------------
-- 5. 友链表
-- ----------------------------
DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '友链名称',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '友链地址',
  `avatar` varchar(500) NOT NULL DEFAULT '' COMMENT '头像URL',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0隐藏 1显示',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='友链表';

-- 示例友链
INSERT INTO `friends` (`name`, `url`, `avatar`, `description`, `sort_order`) VALUES
('QClaw', 'https://github.com/openclaw/openclaw', '', '本地 AI 助手', 1);

-- ----------------------------
-- 6. 时间线表
-- ----------------------------
DROP TABLE IF EXISTS `timeline`;
CREATE TABLE `timeline` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `event_date` date NOT NULL COMMENT '事件日期',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标类名',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`event_date`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='时间线表';

-- ----------------------------
-- 7. 评论表
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章ID（0为留言板）',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父评论ID',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `website` varchar(200) NOT NULL DEFAULT '' COMMENT '网站',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `user_agent` varchar(500) NOT NULL DEFAULT '' COMMENT 'UA',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0待审 1通过 2垃圾',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post` (`post_id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- ----------------------------
-- 8. 站点配置表
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_key` varchar(50) NOT NULL DEFAULT '' COMMENT '配置键',
  `site_value` text COMMENT '配置值',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '说明',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`site_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点配置表';

-- 默认配置
INSERT INTO `settings` (`site_key`, `site_value`, `description`) VALUES
('site_title', '我的博客', '网站标题'),
('site_author', '你的名字', '作者名'),
('site_bio', '在这里记录生活...', '个人简介'),
('site_avatar', '', '头像URL'),
('nav_title', 'MyBlog', '导航栏名称'),
('theme_colors', '#a18cd1,#fbc2eb,#a1c4fd,#c2e9fb', '主题颜色（逗号分隔）'),
('default_cover', '', '默认文章封面'),
('social_github', '', 'GitHub 地址'),
('social_twitter', '', 'Twitter 地址'),
('social_email', '', '邮箱地址'),
('social_wechat', '', '微信二维码'),
('footer_text', 'Powered by MyGlassBlog PHP', '页脚文字'),
('posts_per_page', '10', '每页文章数'),
('chatters_per_page', '20', '每页说说数');

-- ----------------------------
-- 9. 访问日志表（可选）
-- ----------------------------
DROP TABLE IF EXISTS `visits`;
CREATE TABLE `visits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` varchar(500) NOT NULL DEFAULT '' COMMENT '访问路径',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `user_agent` varchar(500) NOT NULL DEFAULT '' COMMENT 'UA',
  `referer` varchar(500) NOT NULL DEFAULT '' COMMENT '来源',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_path` (`path`(191)),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问日志表';

-- ----------------------------
-- 10. 捐款/赞赏表
-- ----------------------------
DROP TABLE IF EXISTS `donations`;
CREATE TABLE `donations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) NOT NULL DEFAULT '' COMMENT '平台名称（如：微信支付、支付宝）',
  `qrcode` varchar(500) NOT NULL DEFAULT '' COMMENT '收款码图片URL',
  `account_name` varchar(100) NOT NULL DEFAULT '' COMMENT '账户名称/昵称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '描述文字',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用：0停用 1启用',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序（越小越靠前）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='捐款/赞赏表';

SET FOREIGN_KEY_CHECKS = 1;
