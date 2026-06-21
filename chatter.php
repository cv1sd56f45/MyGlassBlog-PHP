<?php
/**
 * MyGlassBlog PHP - 说说页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$chatterModel = new Chatter();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = intval($settings->get('chatters_per_page', 20));
$chatters = $chatterModel->getList($page, $perPage);
$total = $chatterModel->getCount();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-8 text-center">💬 说说</h1>

<div class="max-w-2xl mx-auto space-y-4">
    <?php if (empty($chatters)): ?>
        <div class="glass rounded-xl p-8 text-center opacity-70">
            暂无说说
        </div>
    <?php else: ?>
        <?php foreach ($chatters as $chatter): ?>
            <div class="glass rounded-xl p-6">
                <div class="whitespace-pre-wrap mb-3"><?= e($chatter['content']) ?></div>
                <?php if ($chatter['images']): ?>
                    <?php $images = json_decode($chatter['images'], true); ?>
                    <?php if ($images): ?>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <?php foreach ($images as $img): ?>
                                <img src="<?= e($img) ?>" class="rounded-lg object-cover aspect-square cursor-pointer hover:opacity-80 transition-opacity">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="text-sm opacity-50"><?= format_time($chatter['created_at'], 'Y-m-d H:i') ?></div>
            </div>
        <?php endforeach; ?>
        
        <?= pagination($page, $total, $perPage, site_url('chatter.php')) ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
