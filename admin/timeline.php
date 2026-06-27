<?php
/**
 * MyGlassBlog PHP - 时间线管理
 */
require_once 'common.php';
admin_header('时间线管理');

$timelineModel = new Timeline();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        if ($timelineModel->delete($id)) {
            $message = '时间线记录已删除';
        } else {
            $error = '删除失败';
        }
    } else if (isset($_POST['save'])) {
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content']),
            'event_date' => trim($_POST['date'] ?? date('Y-m-d')),
            'icon' => trim($_POST['icon'] ?? 'fas fa-star'),
            'type' => trim($_POST['type'] ?? 'event'),
            'sort_order' => intval($_POST['sort_order'] ?? 0),
        ];
        
        // 确保 event_date 不为空
        if (empty($data['event_date'])) {
            $data['event_date'] = date('Y-m-d');
        }
        
        if (empty($data['title']) || empty($data['content'])) {
            $error = '标题和内容不能为空';
        } else {
            if ($id > 0) {
                $timelineModel->update($id, $data);
                $message = '时间线已更新';
            } else {
                $timelineModel->create($data);
                $message = '时间线已添加';
            }
            $action = 'list';
        }
    }
}

// 获取数据
$items = $timelineModel->getList();
$total = count($items);

$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editItem = $timelineModel->getById(intval($_GET['id']));
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
            <?= $editItem ? '编辑时间线' : '添加时间线' ?>
        </h2>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editItem['id'] ?? 0 ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">标题 *</label>
                    <input type="text" name="title" required
                           value="<?= e($editItem['title'] ?? '') ?>"
                           placeholder="如：项目上线"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">日期</label>
                    <input type="date" name="date"
                           value="<?= e($editItem['date'] ?? date('Y-m-d')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">图标（Font Awesome）</label>
                    <input type="text" name="icon"
                           value="<?= e($editItem['icon'] ?? 'fas fa-star') ?>"
                           placeholder="如：fas fa-code, fas fa-heart"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">参考：<a href="https://fontawesome.com/icons" target="_blank" class="text-blue-500 hover:underline">FontAwesome 图标库</a></p>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">类型</label>
                    <select name="type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="event" <?= ($editItem['type'] ?? '') == 'event' ? 'selected' : '' ?>>事件 📌</option>
                        <option value="milestone" <?= ($editItem['type'] ?? '') == 'milestone' ? 'selected' : '' ?>>里程碑 🏆</option>
                        <option value="work" <?= ($editItem['type'] ?? '') == 'work' ? 'selected' : '' ?>>工作 💼</option>
                        <option value="life" <?= ($editItem['type'] ?? '') == 'life' ? 'selected' : '' ?>>生活 🌱</option>
                        <option value="study" <?= ($editItem['type'] ?? '') == 'study' ? 'selected' : '' ?>>学习 📚</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">排序</label>
                    <input type="number" name="sort_order"
                           value="<?= $editItem['sort_order'] ?? 0 ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">内容 *</label>
                    <textarea name="content" rows="4" required
                              placeholder="详细描述这个时刻..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= e($editItem['content'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="submit" name="save" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-save mr-2"></i> 保存
                </button>
                <a href="<?= site_url('admin/timeline.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    取消
                </a>
            </div>
        </form>
    </div>
    
<?php else: ?>
    <!-- 时间线列表 -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-gray-800">全部记录 (<?= $total ?>)</h2>
            <a href="<?= site_url('admin/timeline.php?action=new') ?>" 
               class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <i class="fas fa-plus mr-2"></i> 添加记录
            </a>
        </div>
        
        <?php if (empty($items)): ?>
            <div class="p-8 text-center text-gray-500">
                暂无记录，<a href="<?= site_url('admin/timeline.php?action=new') ?>" class="text-blue-500 hover:underline">点击添加</a>
            </div>
        <?php else: ?>
            <!-- 时间线样式列表 -->
            <div class="p-6">
                <div class="relative">
                    <!-- 中轴线 -->
                    <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <?php foreach ($items as $item): ?>
                        <div class="relative flex gap-6 pb-8 last:pb-0">
                            <!-- 圆点 -->
                            <div class="flex-shrink-0 w-16 flex justify-center">
                                <div class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center z-10 shadow-sm mt-1">
                                    <i class="<?= e($item['icon'] ?? 'fas fa-star') ?> text-xs"></i>
                                </div>
                            </div>
                            
                            <!-- 内容卡片 -->
                            <div class="flex-1 bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-800"><?= e($item['title']) ?></span>
                                    <?php if (!empty($item['type'])): ?>
                                        <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-600 rounded-full">
                                            <?= e($item['type']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-2"><?= e($item['content']) ?></p>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">
                                        <i class="fas fa-calendar mr-1"></i> <?= e($item['date']) ?: '未设置日期' ?>
                                    </span>
                                    
                                    <div class="flex gap-2">
                                        <a href="<?= site_url('admin/timeline.php?action=edit&id=' . $item['id']) ?>"
                                           class="text-blue-500 hover:text-blue-600 text-sm">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('确定删除？')">
                                            <button type="submit" name="delete" value="<?= $item['id'] ?>"
                                                    class="text-red-500 hover:text-red-600 text-sm">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php admin_footer(); ?>
