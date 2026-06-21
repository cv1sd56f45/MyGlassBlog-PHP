<?php
/**
 * MyGlassBlog PHP - 文章列表页
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$postModel = new Post();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = intval($settings->get('posts_per_page', 10));
$posts = $postModel->getList($page, $perPage);
$total = $postModel->getCount();
$categories = $postModel->getCategories();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-8 text-center">📝 全部文章</h1>

<?php if ($categories): ?>
<div class="glass rounded-xl p-4 mb-8 flex flex-wrap gap-2">
    <span class="font-bold">分类：</span>
    <?php foreach ($categories as $cat): ?>
        <span class="glass px-3 py-1 rounded-full text-sm cursor-pointer hover:bg-white/20 transition-colors"
              onclick="filterByCategory('<?= e($cat['category']) ?>')">
            <?= e($cat['category']) ?>
        </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="glass rounded-xl p-8 text-center opacity-70">
        暂无文章
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($posts as $post): ?>
            <article class="glass rounded-xl p-6 post-card">
                <a href="<?= site_url('post.php?slug=' . e($post['slug'])) ?>" class="block group">
                    <div class="flex gap-6">
                        <?php if ($post['cover']): ?>
                            <img src="<?= e($post['cover']) ?>" alt="<?= e($post['title']) ?>" 
                                 class="w-32 h-24 object-cover rounded-lg flex-shrink-0">
                        <?php endif; ?>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold mb-2 group-hover:opacity-80 transition-opacity">
                                <?= e($post['title']) ?>
                            </h2>
                            <p class="opacity-70 mb-3 line-clamp-2">
                                <?= e($post['description'] ?: truncate(strip_tags($post['content']), 150)) ?>
                            </p>
                            <div class="flex items-center gap-4 text-sm opacity-60">
                                <span><?= format_time($post['created_at'], 'Y-m-d') ?></span>
                                <?php if ($post['category']): ?>
                                    <span class="glass px-2 py-1 rounded"><?= e($post['category']) ?></span>
                                <?php endif; ?>
                                <span>👁 <?= $post['views'] ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
    
    <?= pagination($page, $total, $perPage, site_url('posts.php')) ?>
<?php endif; ?>

<script>
function filterByCategory(category) {
    // 简单的分类筛选（前端实现）
    // 实际项目中应该通过URL参数传递后端筛选
    alert('筛选分类: ' + category + '（功能待实现）');
}
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
