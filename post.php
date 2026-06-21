<?php
/**
 * MyGlassBlog PHP - 文章详情页
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$postModel = new Post();
$commentModel = new Comment();

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    redirect(site_url('posts.php'));
}

$post = $postModel->getBySlug($slug);
if (!$post) {
    http_response_code(404);
    require_once __DIR__ . '/templates/header.php';
    echo '<div class="glass rounded-xl p-8 text-center"><h1 class="text-2xl font-bold mb-4">文章不存在</h1><a href="' . site_url('posts.php') . '" class="text-blue-500">返回文章列表</a></div>';
    require_once __DIR__ . '/templates/footer.php';
    exit;
}

// 增加阅读量
$postModel->incrementViews($post['id']);

// 获取评论
$comments = $commentModel->getByPostId($post['id']);

// 处理评论提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $commentData = [
        'post_id' => $post['id'],
        'nickname' => trim($_POST['nickname'] ?? '匿名'),
        'email' => trim($_POST['email'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'ip' => get_client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'status' => 1, // 默认通过，可改为0需要审核
    ];
    
    if (!empty($commentData['content'])) {
        $commentModel->create($commentData);
        redirect(site_url('post.php?slug=' . $slug . '#comments'));
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<!-- 文章标题 -->
<div class="glass rounded-2xl p-8 mb-8">
    <h1 class="text-3xl font-bold mb-4"><?= e($post['title']) ?></h1>
    <div class="flex items-center gap-4 text-sm opacity-60">
        <span>📅 <?= format_time($post['created_at'], 'Y-m-d') ?></span>
        <?php if ($post['category']): ?>
            <span class="glass px-2 py-1 rounded"><?= e($post['category']) ?></span>
        <?php endif; ?>
        <span>👁 <?= $post['views'] ?> 阅读</span>
    </div>
</div>

<!-- 文章内容 -->
<article class="glass rounded-2xl p-8 mb-8">
    <div class="markdown">
        <?= markdown_to_html($post['content']) ?>
    </div>
</article>

<!-- 评论区 -->
<div id="comments" class="glass rounded-2xl p-8">
    <h2 class="text-xl font-bold mb-6">💬 评论 (<?= count($comments) ?>)</h2>
    
    <!-- 评论表单 -->
    <form method="POST" class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <input type="text" name="nickname" placeholder="昵称" required
                   class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
            <input type="email" name="email" placeholder="邮箱（选填）"
                   class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
        </div>
        <textarea name="content" rows="4" placeholder="说点什么..." required
                  class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40 mb-4"></textarea>
        <button type="submit" name="comment" class="px-6 py-2 rounded-lg bg-white/20 hover:bg-white/30 transition-colors font-medium">
            发表评论
        </button>
    </form>
    
    <!-- 评论列表 -->
    <?php if (empty($comments)): ?>
        <p class="opacity-60 text-center">暂无评论，快来抢沙发吧！</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($comments as $comment): ?>
                <div class="border-b border-white/10 pb-4 last:border-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-medium"><?= e($comment['nickname']) ?></span>
                        <span class="text-xs opacity-50"><?= relative_time($comment['created_at']) ?></span>
                    </div>
                    <p class="opacity-80"><?= nl2br(e($comment['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
