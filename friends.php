<?php
/**
 * MyGlassBlog PHP - 友链页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$friendModel = new Friend();
$friends = $friendModel->getList();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-8 text-center">🔗 友情链接</h1>

<div class="glass rounded-xl p-6 mb-8 text-center opacity-70">
    <p>期待与志同道合的朋友互换友链，欢迎留言或邮件联系我！</p>
</div>

<?php if (empty($friends)): ?>
    <div class="glass rounded-xl p-8 text-center opacity-70">
        暂无友链
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($friends as $friend): ?>
            <a href="<?= e($friend['url']) ?>" target="_blank" 
               class="glass rounded-xl p-4 flex items-center gap-4 hover:bg-white/20 transition-colors group">
                <?php if ($friend['avatar']): ?>
                    <img src="<?= e($friend['avatar']) ?>" alt="<?= e($friend['name']) ?>" 
                         class="w-16 h-16 rounded-full object-cover flex-shrink-0">
                <?php else: ?>
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">👤</span>
                    </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold group-hover:underline"><?= e($friend['name']) ?></h3>
                    <p class="text-sm opacity-70 truncate"><?= e($friend['description']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
