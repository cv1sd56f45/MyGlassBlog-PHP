<?php
/**
 * MyGlassBlog PHP - 捐款/赞赏管理
 */
require_once 'common.php';
admin_header('捐款管理');

$donateModel = new Donate();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 上传目录
$uploadDir = __DIR__ . '/../uploads/donate/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        $item = $donateModel->delete($id);
        if ($item) {
            // 清理二维码文件
            if (!empty($item['qrcode'])) {
                $filePath = __DIR__ . '/../' . ltrim($item['qrcode'], '/');
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $message = '平台已删除';
        } else {
            $error = '删除失败';
        }
    } else if (isset($_POST['save'])) {
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'platform' => trim($_POST['platform']),
            'account_name' => trim($_POST['account_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'sort_order' => intval($_POST['sort_order'] ?? 0),
            'qrcode' => trim($_POST['qrcode'] ?? ''),
        ];
        
        if (empty($data['platform'])) {
            $error = '平台名称不能为空';
        } else {
            // 处理二维码上传
            if (isset($_FILES['qrcode_file']) && $_FILES['qrcode_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['qrcode_file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                
                if (in_array($ext, $allowedExts)) {
                    $filename = 'donate_' . time() . '_' . uniqid() . '.' . $ext;
                    $destPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        // 如果更新且已有旧二维码，删除旧文件
                        if ($id > 0) {
                            $old = $donateModel->getById($id);
                            if ($old && !empty($old['qrcode'])) {
                                $oldFilePath = __DIR__ . '/../' . ltrim($old['qrcode'], '/');
                                if (file_exists($oldFilePath)) {
                                    @unlink($oldFilePath);
                                }
                            }
                        }
                        $data['qrcode'] = '/uploads/donate/' . $filename;
                    } else {
                        $error = '文件上传失败';
                    }
                } else {
                    $error = '仅支持 jpg/png/gif/webp/svg 格式';
                }
            }
            
            if (empty($error)) {
                if ($id > 0) {
                    $donateModel->update($id, $data);
                    $message = '平台已更新';
                } else {
                    $donateModel->create($data);
                    $message = '平台已添加';
                }
                $action = 'list';
            }
        }
    }
}

// 获取数据
$donations = $donateModel->getList(false);
$total = count($donations);

$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editItem = $donateModel->getById(intval($_GET['id']));
}
?>
<style>
.form-input { width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; transition: all 0.2s; }
.form-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.3); border-color: #3b82f6; }
</style>

<?php if ($message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($action === 'edit' || $action === 'new'): ?>
    <!-- 编辑表单 -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-6">
            <?= $editItem ? '编辑收款平台' : '添加收款平台' ?>
        </h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $editItem['id'] ?? 0 ?>">
            <input type="hidden" name="qrcode" value="<?= e($editItem['qrcode'] ?? '') ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">平台名称 *</label>
                    <input type="text" name="platform" required
                           value="<?= e($editItem['platform'] ?? '') ?>"
                           placeholder="如：微信支付、支付宝"
                           class="form-input">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">账户名称</label>
                    <input type="text" name="account_name"
                           value="<?= e($editItem['account_name'] ?? '') ?>"
                           placeholder="如：张三"
                           class="form-input">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">收款码图片</label>
                    <input type="file" name="qrcode_file" accept="image/*"
                           class="form-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <?php if (!empty($editItem['qrcode'])): ?>
                        <div class="mt-2 flex items-center gap-3">
                            <img src="<?= e(site_url(ltrim($editItem['qrcode'], '/'))) ?>" alt="当前收款码"
                                 class="w-16 h-16 object-cover rounded-lg border">
                            <span class="text-xs text-gray-400">当前图片，上传新图会替换</span>
                        </div>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-1">支持 jpg/png/gif/webp/svg 格式</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">排序（数字越小越靠前）</label>
                    <input type="number" name="sort_order"
                           value="<?= $editItem['sort_order'] ?? 0 ?>"
                           class="form-input">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">描述文字</label>
                    <textarea name="description" rows="2"
                              placeholder="如：扫一扫，支持博主"
                              class="form-input"><?= e($editItem['description'] ?? '') ?></textarea>
                </div>
                
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="is_enabled" id="is_enabled"
                           <?= ($editItem['is_enabled'] ?? 1) == 1 ? 'checked' : '' ?>
                           class="w-4 h-4 text-blue-500 rounded">
                    <label for="is_enabled" class="text-gray-700">启用（在前台显示）</label>
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="submit" name="save" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-save mr-2"></i> 保存
                </button>
                <a href="<?= site_url('admin/donate.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    取消
                </a>
            </div>
        </form>
    </div>
    
<?php else: ?>
    <!-- 捐款平台列表 -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-gray-800">全部收款平台 (<?= $total ?>)</h2>
            <a href="<?= site_url('admin/donate.php?action=new') ?>" 
               class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <i class="fas fa-plus mr-2"></i> 添加平台
            </a>
        </div>
        
        <?php if (empty($donations)): ?>
            <div class="p-8 text-center text-gray-500">
                暂无收款平台，<a href="<?= site_url('admin/donate.php?action=new') ?>" class="text-blue-500 hover:underline">点击添加</a>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium w-48">平台</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">账户</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">描述</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium w-20">收款码</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium w-16">状态</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium w-16">排序</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-medium w-32">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $d): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800"><?= e($d['platform']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($d['account_name']) ?: '-' ?></td>
                            <td class="px-4 py-3 text-gray-500 text-sm truncate max-w-xs"><?= e($d['description']) ?: '-' ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if (!empty($d['qrcode'])): ?>
                                    <a href="<?= e(site_url(ltrim($d['qrcode'], '/'))) ?>" target="_blank">
                                        <img src="<?= e(site_url(ltrim($d['qrcode'], '/'))) ?>" alt="收款码"
                                             class="w-10 h-10 object-cover rounded inline-block border">
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">无</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($d['is_enabled'] == 1): ?>
                                    <span class="text-green-500 text-sm">启用</span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">停用</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-sm"><?= $d['sort_order'] ?? 0 ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= site_url('admin/donate.php?action=edit&id=' . $d['id']) ?>"
                                   class="text-blue-500 hover:text-blue-600 mr-2" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('确定删除「<?= e($d['platform']) ?>」？')">
                                    <button type="submit" name="delete" value="<?= $d['id'] ?>"
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
