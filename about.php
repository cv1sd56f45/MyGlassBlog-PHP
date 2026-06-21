<?php
/**
 * MyGlassBlog PHP - 关于页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-8 text-center">👤 关于我</h1>

<div class="max-w-2xl mx-auto">
    <div class="glass rounded-2xl p-8 text-center mb-8">
        <?php $avatar = $settings->get('site_avatar'); ?>
        <?php if ($avatar): ?>
            <img src="<?= e($avatar) ?>" alt="Avatar" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover ring-4 ring-white/20">
        <?php endif; ?>
        <h2 class="text-2xl font-bold mb-2"><?= e($settings->get('site_author')) ?></h2>
        <p class="opacity-70 mb-6"><?= e($settings->get('site_bio')) ?></p>
        
        <div class="flex justify-center gap-4">
            <?php if ($settings->get('social_github')): ?>
                <a href="<?= e($settings->get('social_github')) ?>" target="_blank" 
                   class="glass px-4 py-2 rounded-lg hover:bg-white/20 transition-colors">GitHub</a>
            <?php endif; ?>
            <?php if ($settings->get('social_twitter')): ?>
                <a href="<?= e($settings->get('social_twitter')) ?>" target="_blank" 
                   class="glass px-4 py-2 rounded-lg hover:bg-white/20 transition-colors">Twitter</a>
            <?php endif; ?>
            <?php if ($settings->get('social_email')): ?>
                <a href="mailto:<?= e($settings->get('social_email')) ?>" 
                   class="glass px-4 py-2 rounded-lg hover:bg-white/20 transition-colors">邮箱</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="glass rounded-xl p-6">
        <h3 class="font-bold text-lg mb-4">关于本站</h3>
        <p class="opacity-70 leading-relaxed">
            本站使用 <strong>MyGlassBlog PHP</strong> 构建，这是一个简洁优雅的毛玻璃风格博客系统。
        </p>
        <ul class="mt-4 space-y-2 opacity-70">
            <li>✨ 毛玻璃设计风格</li>
            <li>🌙 支持暗色模式</li>
            <li>📝 Markdown 写作</li>
            <li>💬 说说 / 📷 照片墙 / 🔗 友链 / 📅 时间线</li>
            <li>🛠️ PHP + MySQL，宝塔面板友好部署</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
