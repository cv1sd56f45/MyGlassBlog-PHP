<?php
/**
 * MyGlassBlog PHP - 照片墙管理
 */
require_once 'common.php';
admin_header('照片管理');

$photoModel = new Photo();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        $photo = $photoModel->getById($id);
        if ($photo && !empty($photo['path'])) {
            @unlink(__DIR__ . '/../' . $photo['path']);
        }
        if ($photoModel->delete($id)) {
            $message = '照片已删除';
        } else {
            $error = '删除失败';
        }
    } else if (isset($_POST['save'])) {
        // 上传照片
        if (!empty($_FILES['photo']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../uploads/photos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $filename = date('YmdHis') . '_' . rand(1000, 9999) . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $filepath)) {
                    $photoUrl = 'uploads/photos/' . $filename;
                    $data = [
                        'title' => trim($_POST['title'] ?? ''),
                        'description' => trim($_POST['description'] ?? ''),
                        'url' => $photoUrl,
                        'thumb' => $photoUrl,
                    ];
                    $photoModel->create($data);
                    $message = '照片已上传';
                } else {
                    $error = '上传失败，请检查目录权限';
                }
            } else {
                $error = '不支持的文件格式（仅支持 jpg/png/gif/webp）';
            }
        } else {
            $error = '请选择要上传的照片';
        }
    }
}

// 获取数据
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$photos = $photoModel->getList($page, 20);
$total = $photoModel->getCount();
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

<!-- 上传区域 -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">上传新照片</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">选择图片</label>
                <input type="file" name="photo" accept="image/*" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">标题</label>
                <input type="text" name="title"
                       placeholder="可选，留空自动使用文件名"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="block text-gray-700 font-medium mb-2">描述</label>
            <textarea name="description" rows="2"
                      placeholder="可选的描述文字"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <button type="submit" name="save" class="mt-4 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
            <i class="fas fa-upload mr-2"></i> 上传
        </button>
    </form>
</div>

<!-- 照片列表 -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-4 border-b">
        <h2 class="font-bold text-gray-800">全部照片 (<?= $total ?>)</h2>
    </div>
    
    <?php if (empty($photos)): ?>
        <div class="p-8 text-center text-gray-500">
            暂无照片，请使用上方表单上传
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
            <?php foreach ($photos as $photo): ?>
                <div class="group relative bg-gray-50 rounded-lg overflow-hidden border hover:shadow-md transition-shadow">
                    <?php
                    $imgPath = site_url($photo['thumb'] ?? $photo['url'] ?? '');
                    ?>
                    <a href="<?= e($imgPath) ?>" target="_blank">
                        <img src="<?= e($imgPath) ?>" alt="<?= e($photo['title']) ?>"
                             class="w-full h-32 object-cover"
                             onerror="this.src='https://via.placeholder.com/200x150?text=No+Image'">
                    </a>
                    
                    <div class="p-2">
                        <p class="text-sm text-gray-700 truncate" title="<?= e($photo['title']) ?>">
                            <?= e($photo['title']) ?: '无标题' ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            <?= format_time($photo['created_at'], 'Y-m-d') ?>
                        </p>
                    </div>
                    
                    <form method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity"
                          onsubmit="return confirm('确定删除这张照片？')">
                        <button type="submit" name="delete" value="<?= $photo['id'] ?>"
                                class="bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center hover:bg-red-600 shadow">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php admin_footer(); ?>
