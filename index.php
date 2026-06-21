<?php
/**
 * MyGlassBlog PHP - 首页
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$postModel = new Post();
$chatterModel = new Chatter();
$photoModel = new Photo();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = intval($settings->get('posts_per_page', 10));
$posts = $postModel->getList($page, $perPage);
$total = $postModel->getCount();
$chatters = $chatterModel->getLatest(3);
$photos = $photoModel->getLatest(6);

require_once __DIR__ . '/templates/header.php';
?>

<!-- 个人卡片 -->
<div class="glass rounded-2xl p-8 mb-8 text-center">
    <?php $avatar = $settings->get('site_avatar'); ?>
    <?php if ($avatar): ?>
        <img src="<?= e($avatar) ?>" alt="Avatar" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover ring-4 ring-white/20">
    <?php endif; ?>
    <h1 class="text-3xl font-bold mb-2"><?= e($settings->get('site_title')) ?></h1>
    <p class="opacity-70 mb-4"><?= e($settings->get('site_bio')) ?></p>
    
    <!-- 社交链接 -->
    <div class="flex justify-center gap-4">
        <?php if ($settings->get('social_github')): ?>
            <a href="<?= e($settings->get('social_github')) ?>" target="_blank" class="glass px-4 py-2 rounded-lg hover:bg-white/20 transition-colors">GitHub</a>
        <?php endif; ?>
        <?php if ($settings->get('social_email')): ?>
            <a href="mailto:<?= e($settings->get('social_email')) ?>" class="glass px-4 py-2 rounded-lg hover:bg-white/20 transition-colors">邮箱</a>
        <?php endif; ?>
    </div>
</div>

<!-- 主内容区：左侧文章，右侧边栏 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- 左侧：文章列表 -->
    <div class="lg:col-span-2">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <span>📝</span> 最新文章
        </h2>
        
        <?php if (empty($posts)): ?>
            <div class="glass rounded-xl p-8 text-center opacity-70">
                暂无文章
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="glass rounded-xl p-6 mb-4 post-card">
                    <a href="<?= site_url('post.php?slug=' . e($post['slug'])) ?>" class="block group">
                        <h3 class="text-xl font-bold mb-2 group-hover:opacity-80 transition-opacity">
                            <?= e($post['title']) ?>
                        </h3>
                        <p class="opacity-70 mb-3 line-clamp-2">
                            <?= e(truncate(strip_tags($post['content']), 150)) ?>
                        </p>
                        <div class="flex items-center gap-4 text-sm opacity-60">
                            <span><?= format_time($post['created_at'], 'Y-m-d') ?></span>
                            <?php if ($post['category']): ?>
                                <span class="glass px-2 py-1 rounded"><?= e($post['category']) ?></span>
                            <?php endif; ?>
                            <span>👁 <?= $post['views'] ?></span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
            
            <?= pagination($page, $total, $perPage, site_url('index.php')) ?>
        <?php endif; ?>
    </div>
    
    <!-- 右侧边栏 -->
    <div class="space-y-6">
        <!-- 最新说说 -->
        <div class="glass rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span>💬</span> 最新说说
            </h3>
            <?php foreach ($chatters as $chatter): ?>
                <div class="mb-3 pb-3 border-b border-white/10 last:border-0">
                    <p class="text-sm opacity-80"><?= e(truncate($chatter['content'], 80)) ?></p>
                    <span class="text-xs opacity-50"><?= relative_time($chatter['created_at']) ?></span>
                </div>
            <?php endforeach; ?>
            <a href="<?= site_url('chatter.php') ?>" class="text-sm opacity-60 hover:opacity-100 transition-opacity">
                查看全部 →
            </a>
        </div>
        
        <!-- 照片墙预览 -->
        <div class="glass rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span>📷</span> 照片墙
            </h3>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach ($photos as $photo): ?>
                    <img src="<?= e($photo['thumb']) ?>" alt="<?= e($photo['title']) ?>" 
                         class="w-full aspect-square object-cover rounded-lg hover:opacity-80 transition-opacity cursor-pointer"
                         onclick="window.location.href='<?= site_url('photowall.php') ?>'">
                <?php endforeach; ?>
            </div>
            <a href="<?= site_url('photowall.php') ?>" class="block text-center mt-4 text-sm opacity-60 hover:opacity-100 transition-opacity">
                查看全部 →
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
