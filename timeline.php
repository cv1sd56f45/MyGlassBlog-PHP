<?php
/**
 * MyGlassBlog PHP - 时间线页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$timelineModel = new Timeline();
$events = $timelineModel->getList();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-8 text-center">📅 时间线</h1>

<?php if (empty($events)): ?>
    <div class="glass rounded-xl p-8 text-center opacity-70">
        暂无记录
    </div>
<?php else: ?>
    <div class="max-w-2xl mx-auto">
        <div class="relative pl-8 border-l-2 border-white/20">
            <?php foreach ($events as $event): ?>
                <div class="relative mb-8 last:mb-0">
                    <!-- 时间点 -->
                    <div class="absolute -left-3 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                        <span class="w-3 h-3 rounded-full bg-white"></span>
                    </div>
                    
                    <div class="glass rounded-xl p-6 ml-4">
                        <div class="text-sm opacity-50 mb-2">
                            <?= format_time($event['event_date'], 'Y年m月d日') ?>
                        </div>
                        <h3 class="font-bold text-lg mb-2"><?= e($event['title']) ?></h3>
                        <?php if ($event['content']): ?>
                            <p class="opacity-70"><?= e($event['content']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
