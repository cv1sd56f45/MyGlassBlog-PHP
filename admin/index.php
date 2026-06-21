<?php
/**
 * MyGlassBlog PHP - 后台首页
 */
require_once 'common.php';
admin_header('仪表盘');

$postModel = new Post();
$chatterModel = new Chatter();
$photoModel = new Photo();
$commentModel = new Comment();
$db = Database::getInstance();

$stats = [
    'posts' => $postModel->getCount(),
    'chatters' => $chatterModel->getCount(),
    'photos' => $db->queryValue("SELECT COUNT(*) FROM photos WHERE status = 1"),
    'comments' => $db->queryValue("SELECT COUNT(*) FROM comments WHERE status = 1"),
];

$recentPosts = $postModel->getList(1, 5);
$recentComments = $commentModel->getRecent(5);
?>

<!-- 统计卡片 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">文章数量</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['posts'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-file-alt text-blue-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">说说数量</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['chatters'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-comment text-green-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">照片数量</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['photos'] ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-images text-purple-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">评论数量</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['comments'] ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-comments text-orange-500 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- 最近文章和评论 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-800">📝 最近文章</h2>
        </div>
        <div class="p-4">
            <?php if (empty($recentPosts)): ?>
                <p class="text-gray-500 text-center py-4">暂无文章</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($recentPosts as $post): ?>
                        <li class="flex items-center justify-between py-2 border-b last:border-0">
                            <a href="<?= site_url('admin/posts.php?action=edit&id=' . $post['id']) ?>" 
                               class="text-gray-700 hover:text-blue-500 truncate flex-1">
                                <?= e($post['title']) ?>
                            </a>
                            <span class="text-xs text-gray-400 ml-4"><?= format_time($post['created_at'], 'm-d') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-800">💬 最近评论</h2>
        </div>
        <div class="p-4">
            <?php if (empty($recentComments)): ?>
                <p class="text-gray-500 text-center py-4">暂无评论</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($recentComments as $comment): ?>
                        <li class="py-2 border-b last:border-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-medium text-gray-700"><?= e($comment['nickname']) ?></span>
                                <span class="text-xs text-gray-400"><?= relative_time($comment['created_at']) ?></span>
                            </div>
                            <p class="text-sm text-gray-500 truncate"><?= e($comment['content']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php admin_footer(); ?>
