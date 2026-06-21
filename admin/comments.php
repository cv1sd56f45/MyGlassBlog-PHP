<?php
/**
 * MyGlassBlog PHP - 评论管理
 */
require_once 'common.php';
admin_header('评论管理');

$commentModel = new Comment();
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        if ($commentModel->delete($id)) {
            $message = '评论已删除';
        } else {
            $error = '删除失败';
        }
    } else if (isset($_POST['batch_delete']) && !empty($_POST['ids'])) {
        $count = 0;
        foreach ($_POST['ids'] as $id) {
            if ($commentModel->delete(intval($id))) {
                $count++;
            }
        }
        $message = "已删除 {$count} 条评论";
    }
}

// 获取数据
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status = $_GET['status'] ?? 'all';

// 筛选
if ($status === 'pending') {
    $comments = $commentModel->getPending($page, 20);
    $total = $commentModel->getPendingCount();
} elseif ($status === 'approved') {
    $comments = $commentModel->getApproved($page, 20);
    $total = $commentModel->getApprovedCount();
} else {
    $comments = $commentModel->getList($page, 20);
    $total = $commentModel->getCount();
}
?>

<?php if ($message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
        <?= e($message) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- 筛选栏 -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-4 flex items-center gap-4">
    <span class="text-gray-600 font-medium">筛选：</span>
    <a href="<?= site_url('admin/comments.php?status=all') ?>" 
       class="px-3 py-1 rounded-full text-sm <?= $status == 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
        全部 (<?= $commentModel->getCount() ?>)
    </a>
    <a href="<?= site_url('admin/comments.php?status=pending') ?>" 
       class="px-3 py-1 rounded-full text-sm <?= $status == 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
        待审核 (<?= $commentModel->getPendingCount() ?? 0 ?>)
    </a>
    <a href="<?= site_url('admin/comments.php?status=approved') ?>" 
       class="px-3 py-1 rounded-full text-sm <?= $status == 'approved' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
        已通过 (<?= $commentModel->getApprovedCount() ?? 0 ?>)
    </a>
</div>

<!-- 评论列表 -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="font-bold text-gray-800">评论列表</h2>
        
        <?php if (!empty($comments)): ?>
            <form method="POST" id="batchForm" onsubmit="return confirm('确定删除选中的评论？')">
                <button type="submit" name="batch_delete"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm">
                    <i class="fas fa-trash mr-2"></i> 批量删除选中
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (empty($comments)): ?>
        <div class="p-8 text-center text-gray-500">
            暂无评论
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-100">
            <?php foreach ($comments as $c): ?>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <!-- 左侧：头像 + 信息 -->
                        <div class="flex gap-3 flex-1 min-w-0">
                            <!-- 头像 -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                                <?= mb_substr(e($c['author'] ?? '?'), 0, 1) ?>
                            </div>
                            
                            <!-- 内容 -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="font-medium text-gray-800"><?= e($c['author'] ?? '匿名') ?></span>
                                    
                                    <?php if (!empty($c['email'])): ?>
                                        <span class="text-xs text-gray-400"><?= e($c['email']) ?></span>
                                    <?php endif; ?>
                                    
                                    <span class="text-xs text-gray-400">
                                        <?= format_time($c['created_at'], 'Y-m-d H:i') ?>
                                    </span>
                                    
                                    <?php if (($c['status'] ?? 1) != 1): ?>
                                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">待审核</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-1"><?= e($c['content']) ?></p>
                                
                                <?php if (!empty($c['post_title'])): ?>
                                    <p class="text-xs text-gray-400">
                                        文章：<a href="<?= site_url('post.php?slug=' . ($c['post_slug'] ?? '')) ?>" target="_blank"
                                               class="text-blue-500 hover:underline"><?= e($c['post_title']) ?></a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- 右侧：操作 -->
                        <div class="flex-shrink-0 flex items-start gap-2">
                            <label class="cursor-pointer mt-1">
                                <input type="checkbox" name="ids[]" value="<?= $c['id'] ?>" form="batchForm"
                                       class="w-4 h-4 text-blue-500 rounded">
                            </label>
                            
                            <form method="POST" class="inline" onsubmit="return confirm('确定删除这条评论？')">
                                <button type="submit" name="delete" value="<?= $c['id'] ?>"
                                        class="text-red-500 hover:text-red-600 p-1" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php admin_footer(); ?>
