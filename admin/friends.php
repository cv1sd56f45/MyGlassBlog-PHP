<?php
/**
 * MyGlassBlog PHP - 友链管理
 */
require_once 'common.php';
admin_header('友链管理');

$friendModel = new Friend();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        if ($friendModel->delete($id)) {
            $message = '友链已删除';
        } else {
            $error = '删除失败';
        }
    } else if (isset($_POST['save'])) {
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'name' => trim($_POST['name']),
            'url' => trim($_POST['url']),
            'logo' => trim($_POST['logo']),
            'description' => trim($_POST['description']),
            'sort_order' => intval($_POST['sort_order'] ?? 0),
            'status' => isset($_POST['status']) ? 1 : 0,
        ];
        
        if (empty($data['name']) || empty($data['url'])) {
            $error = '名称和链接不能为空';
        } else {
            if ($id > 0) {
                $friendModel->update($id, $data);
                $message = '友链已更新';
            } else {
                $friendModel->create($data);
                $message = '友链已添加';
            }
            $action = 'list';
        }
    }
}

// 获取数据
$friends = $friendModel->getList();
$total = count($friends);

$editFriend = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editFriend = $friendModel->getById(intval($_GET['id']));
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
            <?= $editFriend ? '编辑友链' : '添加友链' ?>
        </h2>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editFriend['id'] ?? 0 ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">站点名称 *</label>
                    <input type="text" name="name" required
                           value="<?= e($editFriend['name'] ?? '') ?>"
                           placeholder="如：张三的博客"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">链接地址 *</label>
                    <input type="url" name="url" required
                           value="<?= e($editFriend['url'] ?? '') ?>"
                           placeholder="https://example.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Logo URL</label>
                    <input type="url" name="logo"
                           value="<?= e($editFriend['logo'] ?? '') ?>"
                           placeholder="https://example.com/logo.png"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">排序（数字越小越靠前）</label>
                    <input type="number" name="sort_order"
                           value="<?= $editFriend['sort_order'] ?? 0 ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">描述</label>
                    <input type="text" name="description"
                           value="<?= e($editFriend['description'] ?? '') ?>"
                           placeholder="一句话介绍"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="status" id="status"
                           <?= ($editFriend['status'] ?? 1) == 1 ? 'checked' : '' ?>
                           class="w-4 h-4 text-blue-500 rounded">
                    <label for="status" class="text-gray-700">显示在首页</label>
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="submit" name="save" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-save mr-2"></i> 保存
                </button>
                <a href="<?= site_url('admin/friends.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    取消
                </a>
            </div>
        </form>
    </div>
    
<?php else: ?>
    <!-- 友链列表 -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-gray-800">全部友链 (<?= $total ?>)</h2>
            <a href="<?= site_url('admin/friends.php?action=new') ?>" 
               class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <i class="fas fa-plus mr-2"></i> 添加友链
            </a>
        </div>
        
        <?php if (empty($friends)): ?>
            <div class="p-8 text-center text-gray-500">
                暂无友链，<a href="<?= site_url('admin/friends.php?action=new') ?>" class="text-blue-500 hover:underline">点击添加</a>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">站点</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium w-48">链接</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium w-20">状态</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium w-16">排序</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-medium w-32">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($friends as $f): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($f['logo'])): ?>
                                        <img src="<?= e($f['logo']) ?>" alt="" class="w-8 h-8 rounded-full object-cover"
                                             onerror="this.style.display='none'">
                                    <?php endif; ?>
                                    <span class="font-medium text-gray-800"><?= e($f['name']) ?></span>
                                    <?php if (!empty($f['description'])): ?>
                                        <span class="text-xs text-gray-400"><?= e($f['description']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?= e($f['url']) ?>" target="_blank"
                                   class="text-blue-500 hover:underline text-sm truncate block max-w-xs">
                                    <?= e($f['url']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($f['status'] == 1): ?>
                                    <span class="text-green-500 text-sm">显示中</span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">隐藏</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-sm">
                                <?= $f['sort_order'] ?? 0 ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= e($f['url']) ?>" target="_blank"
                                   class="text-gray-400 hover:text-gray-600 mr-2" title="访问">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="<?= site_url('admin/friends.php?action=edit&id=' . $f['id']) ?>"
                                   class="text-blue-500 hover:text-blue-600 mr-2" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('确定删除？')">
                                    <button type="submit" name="delete" value="<?= $f['id'] ?>"
                                            class="text-red-500 hover:text-red-600" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php admin_footer(); ?>
