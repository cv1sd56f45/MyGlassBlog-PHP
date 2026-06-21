<?php
/**
 * MyGlassBlog PHP - 文章管理
 */
require_once 'common.php';
admin_header('文章管理');

$postModel = new Post();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // 删除
        $id = intval($_POST['delete']);
        if ($postModel->delete($id)) {
            $message = '文章已删除';
        } else {
            $error = '删除失败';
        }
    } else if (isset($_POST['save'])) {
        // 保存
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'title' => trim($_POST['title']),
            'slug' => trim($_POST['slug']) ?: generate_slug($_POST['title']),
            'content' => $_POST['content'],
            'category' => trim($_POST['category']),
            'description' => trim($_POST['description']),
            'cover' => trim($_POST['cover']),
            'status' => intval($_POST['status']),
        ];
        
        if (empty($data['title']) || empty($data['content'])) {
            $error = '标题和内容不能为空';
        } else {
            if ($id > 0) {
                $postModel->update($id, $data);
                $message = '文章已更新';
            } else {
                $id = $postModel->create($data);
                $message = '文章已创建';
            }
            $action = 'list';
        }
    }
}

// 获取数据
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$posts = $postModel->getList($page, 20);
$total = $postModel->getCount();

$editPost = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editPost = $postModel->getById(intval($_GET['id']));
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

<?php if ($action === 'edit' || $action === 'new'): ?>
    <!-- 编辑表单 -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-6">
            <?= $editPost ? '编辑文章' : '新建文章' ?>
        </h2>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editPost['id'] ?? 0 ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 左侧：主要内容 -->
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">标题</label>
                        <input type="text" name="title" required
                               value="<?= e($editPost['title'] ?? '') ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">内容 (Markdown)</label>
                        <textarea name="content" rows="15" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"><?= e($editPost['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">摘要</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= e($editPost['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <!-- 右侧：设置 -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">URL别名</label>
                        <input type="text" name="slug"
                               value="<?= e($editPost['slug'] ?? '') ?>"
                               placeholder="留空自动生成"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">分类</label>
                        <input type="text" name="category"
                               value="<?= e($editPost['category'] ?? '') ?>"
                               placeholder="如：随笔、技术"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">封面图URL</label>
                        <input type="text" name="cover"
                               value="<?= e($editPost['cover'] ?? '') ?>"
                               placeholder="https://..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">状态</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" <?= ($editPost['status'] ?? 1) == 1 ? 'selected' : '' ?>>发布</option>
                            <option value="0" <?= ($editPost['status'] ?? 1) == 0 ? 'selected' : '' ?>>草稿</option>
                        </select>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" name="save" class="w-full py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-save mr-2"></i> 保存
                        </button>
                        <a href="<?= site_url('admin/posts.php') ?>" class="block text-center mt-2 text-gray-500 hover:text-gray-700">
                            取消
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
<?php else: ?>
    <!-- 文章列表 -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-gray-800">全部文章 (<?= $total ?>)</h2>
            <a href="<?= site_url('admin/posts.php?action=new') ?>" 
               class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <i class="fas fa-plus mr-2"></i> 新建文章
            </a>
        </div>
        
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">标题</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium w-24">分类</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium w-20">状态</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium w-32">日期</th>
                    <th class="text-right px-4 py-3 text-gray-600 font-medium w-32">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            暂无文章，<a href="<?= site_url('admin/posts.php?action=new') ?>" class="text-blue-500 hover:underline">点击新建</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="<?= site_url('admin/posts.php?action=edit&id=' . $post['id']) ?>" 
                                   class="text-gray-800 hover:text-blue-500">
                                    <?= e($post['title']) ?>
                                </a>
                                <span class="text-xs text-gray-400 ml-2">👁 <?= $post['views'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= e($post['category']) ?: '-' ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($post['status'] == 1): ?>
                                    <span class="text-green-500 text-sm">已发布</span>
                                <?php else: ?>
                                    <span class="text-yellow-500 text-sm">草稿</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-sm">
                                <?= format_time($post['created_at'], 'Y-m-d') ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= site_url('post.php?slug=' . $post['slug']) ?>" target="_blank"
                                   class="text-gray-400 hover:text-gray-600 mr-2" title="查看">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= site_url('admin/posts.php?action=edit&id=' . $post['id']) ?>"
                                   class="text-blue-500 hover:text-blue-600 mr-2" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('确定删除？')">
                                    <button type="submit" name="delete" value="<?= $post['id'] ?>"
                                            class="text-red-500 hover:text-red-600" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php admin_footer(); ?>
