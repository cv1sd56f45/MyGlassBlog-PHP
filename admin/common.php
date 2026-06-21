<?php
/**
 * MyGlassBlog PHP - 后台公共文件
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// 检查登录
if (!isset($_SESSION['admin_id'])) {
    redirect(site_url('admin/login.php'));
}

$settings = site_config();
$currentUser = $_SESSION['admin_username'] ?? 'Admin';

/**
 * 后台头部模板
 */
function admin_header($title = '管理后台') {
    global $currentUser;
    ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - MyGlassBlog 后台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: calc(100vh - 64px); }
        .menu-item { transition: all 0.2s; }
        .menu-item:hover { background: rgba(255,255,255,0.1); }
        .menu-item.active { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body class="bg-gray-100">
    <!-- 顶部栏 -->
    <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 fixed top-0 left-0 right-0 z-50">
        <h1 class="text-xl font-bold text-gray-800">MyGlassBlog 后台</h1>
        <div class="flex items-center gap-4">
            <a href="<?= site_url() ?>" target="_blank" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-external-link-alt mr-1"></i> 访问前台
            </a>
            <span class="text-gray-400">|</span>
            <span class="text-gray-600">
                <i class="fas fa-user mr-1"></i> <?= e($currentUser) ?>
            </span>
            <a href="<?= site_url('admin/logout.php') ?>" class="text-red-500 hover:text-red-600">
                <i class="fas fa-sign-out-alt"></i> 退出
            </a>
        </div>
    </header>
    
    <div class="flex pt-16">
        <!-- 侧边栏 -->
        <aside class="w-56 bg-gray-800 text-white sidebar fixed">
            <nav class="p-4">
                <a href="<?= site_url('admin/index.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-home w-6"></i> 仪表盘
                </a>
                <a href="<?= site_url('admin/posts.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt w-6"></i> 文章管理
                </a>
                <a href="<?= site_url('admin/chatters.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'chatters.php' ? 'active' : '' ?>">
                    <i class="fas fa-comment w-6"></i> 说说管理
                </a>
                <a href="<?= site_url('admin/photos.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'photos.php' ? 'active' : '' ?>">
                    <i class="fas fa-images w-6"></i> 照片管理
                </a>
                <a href="<?= site_url('admin/friends.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'friends.php' ? 'active' : '' ?>">
                    <i class="fas fa-link w-6"></i> 友链管理
                </a>
                <a href="<?= site_url('admin/timeline.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'timeline.php' ? 'active' : '' ?>">
                    <i class="fas fa-clock w-6"></i> 时间线管理
                </a>
                <a href="<?= site_url('admin/comments.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : '' ?>">
                    <i class="fas fa-comments w-6"></i> 评论管理
                </a>
                <a href="<?= site_url('admin/settings.php') ?>" class="menu-item block px-4 py-3 rounded-lg mb-1 <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog w-6"></i> 站点设置
                </a>
            </nav>
        </aside>
        
        <!-- 主内容 -->
        <main class="flex-1 ml-56 p-6">
<?php
}

/**
 * 后台底部模板
 */
function admin_footer() {
    ?>
        </main>
    </div>
</body>
</html>
<?php
}
