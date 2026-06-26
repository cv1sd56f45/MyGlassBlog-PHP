<?php
/**
 * MyGlassBlog PHP - 头部模板
 */
require_once __DIR__ . '/../includes/functions.php';

$settings = site_config();
$themeColors = $settings->getThemeColors();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($settings->get('site_title', 'MyGlassBlog')) ?></title>
    <meta name="description" content="<?= e($settings->get('site_bio')) ?>">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        glass: 'rgba(255, 255, 255, 0.1)',
                    }
                }
            }
        }
    </script>
    
    <style>
        /* 毛玻璃效果 */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* 背景渐变动画 */
        .gradient-bg {
            background: linear-gradient(-45deg, <?= implode(', ', $themeColors) ?>);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* 悬浮光晕 */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 8s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        /* 文章卡片hover */
        .post-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .post-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* 暗色模式切换 */
        .dark-toggle {
            cursor: pointer;
        }
        
        /* Markdown样式 */
        .markdown h1, .markdown h2, .markdown h3 {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        .markdown h1 { font-size: 1.875rem; }
        .markdown h2 { font-size: 1.5rem; }
        .markdown h3 { font-size: 1.25rem; }
        .markdown p { margin-bottom: 1rem; line-height: 1.75; }
        .markdown pre {
            background: rgba(0, 0, 0, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .markdown code {
            background: rgba(0, 0, 0, 0.1);
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .dark .markdown pre, .dark .markdown code {
            background: rgba(255, 255, 255, 0.1);
        }
        .markdown blockquote {
            border-left: 4px solid currentColor;
            padding-left: 1rem;
            margin: 1rem 0;
            opacity: 0.8;
        }
        .markdown img {
            max-width: 100%;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }
        .markdown ul, .markdown ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }
        .markdown li {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body class="min-h-screen gradient-bg text-gray-800 dark:text-gray-100 transition-colors duration-300">
    <!-- 悬浮光晕装饰 -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="glow-orb w-96 h-96 bg-white/30 dark:bg-white/10 -top-20 -left-20" style="animation-delay: 0s;"></div>
        <div class="glow-orb w-80 h-80 bg-white/20 dark:bg-white/5 top-1/3 right-0" style="animation-delay: 2s;"></div>
        <div class="glow-orb w-72 h-72 bg-white/25 dark:bg-white/10 bottom-0 left-1/3" style="animation-delay: 4s;"></div>
    </div>
    
    <!-- 导航栏 -->
    <nav class="glass sticky top-0 z-50 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="<?= site_url() ?>" class="text-xl font-bold hover:opacity-80 transition-opacity">
                <?= e($settings->get('nav_title', 'MyBlog')) ?>
            </a>
            
            <div class="flex items-center gap-6">
                <a href="<?= site_url() ?>" class="hover:opacity-80 transition-opacity">首页</a>
                <a href="<?= site_url('posts.php') ?>" class="hover:opacity-80 transition-opacity">文章</a>
                <a href="<?= site_url('chatter.php') ?>" class="hover:opacity-80 transition-opacity">说说</a>
                <a href="<?= site_url('photowall.php') ?>" class="hover:opacity-80 transition-opacity">照片墙</a>
                <a href="<?= site_url('friends.php') ?>" class="hover:opacity-80 transition-opacity">友链</a>
                <a href="<?= site_url('timeline.php') ?>" class="hover:opacity-80 transition-opacity">时间线</a>
                <a href="<?= site_url('donate.php') ?>" class="hover:opacity-80 transition-opacity">赞赏</a>
                <a href="<?= site_url('about.php') ?>" class="hover:opacity-80 transition-opacity">关于</a>
                
                <button onclick="toggleDarkMode()" class="dark-toggle p-2 rounded-lg glass hover:bg-white/20 transition-colors" title="切换主题">
                    <span id="darkIcon">🌙</span>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- 主内容区 -->
    <main class="relative z-10 max-w-6xl mx-auto px-6 py-8">
