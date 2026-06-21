<?php
/**
 * MyGlassBlog PHP - 说说管理
 */
require_once 'common.php';
admin_header('说说管理');

$chatterModel = new Chatter();
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        if ($chatterModel->delete(intval($_POST['delete']))) {
            $message = '说说已删除';
        }
    } else if (isset($_POST['create'])) {
        $content = trim($_POST['content']);
        if ($content) {
            $chatterModel->create($content);
            $message = '说说已发布';
        } else {
            $error = '内容不能为空';
        }
    }
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$chatters = $chatterModel->getList($page, 20);
$total = $chatterModel->getCount();
?>

<?php if ($message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4"><?= e($error) ?></div>
<?php endif; ?>

<!-- 发布说说 -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="font-bold text-gray-800 mb-4">发布新说说</h2>
    <form method="POST">
        <textarea name="content" rows="3" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                  placeholder="今天想说点什么..."></textarea>
        <button type="submit" name="create" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            <i class="fas fa-paper-plane mr-2"></i> 发布
        </button>
    </form>
</div>

<!-- 说说列表 -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-4 border-b">
        <h2 class="font-bold text-gray-800">全部说说 (<?= $total ?>)</h2>
    </div>
    
    <div class="divide-y">
        <?php if (empty($chatters)): ?>
            <p class="text-center text-gray-500 py-8">暂无说说</p>
        <?php else: ?>
            <?php foreach ($chatters as $chatter): ?>
                <div class="p-4 flex items-start justify-between">
                    <div class="flex-1">
                        <p class="whitespace-pre-wrap"><?= e($chatter['content']) ?></p>
                        <p class="text-sm text-gray-400 mt-2"><?= format_time($chatter['created_at']) ?></p>
                    </div>
                    <form method="POST" onsubmit="return confirm('确定删除？')">
                        <button type="submit" name="delete" value="<?= $chatter['id'] ?>" 
                                class="text-red-500 hover:text-red-600 ml-4">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php admin_footer(); ?>
