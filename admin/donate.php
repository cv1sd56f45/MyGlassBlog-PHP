<?php
/**
 * MyGlassBlog PHP - 捐款/赞赏管理
 */
require_once __DIR__ . '/common.php';
admin_header('捐款管理');

$donateModel = new Donate();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 处理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save'])) {
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'platform' => trim($_POST['platform']),
            'pay_type' => trim($_POST['pay_type'] ?? 'qrcode'),
            'qrcode' => trim($_POST['qrcode'] ?? ''),
            'link' => trim($_POST['link'] ?? ''),
            'account_name' => trim($_POST['account_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'sort_order' => intval($_POST['sort_order'] ?? 0),
        ];
        
        // 验证
        if (empty($data['platform'])) {
            $error = '平台名称不能为空';
        } elseif (empty($data['pay_type'])) {
            $error = '请选择支付类型';
        } elseif ($data['pay_type'] === 'qrcode' && empty($data['qrcode'])) {
            $error = '请上传收款二维码';
        } elseif ($data['pay_type'] === 'link' && empty($data['link'])) {
            $error = '请填写跳转链接';
        } else {
            // 处理文件上传
            if (!empty($_FILES['qrcode_file']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/donate/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $ext = pathinfo($_FILES['qrcode_file']['name'], PATHINFO_EXTENSION);
                $filename = 'donate_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['qrcode_file']['tmp_name'], $filepath)) {
                    $data['qrcode'] = 'uploads/donate/' . $filename;
                } else {
                    $error = '二维码上传失败';
                }
            }
            
            if (empty($error)) {
                if ($id > 0) {
                    $donateModel->update($id, $data);
                    $message = '捐款方式已更新';
                } else {
                    $donateModel->create($data);
                    $message = '捐款方式已添加';
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        $item = $donateModel->delete($id);
        if ($item) {
            // 删除二维码文件
            if (!empty($item['qrcode'])) {
                $filepath = __DIR__ . '/../' . $item['qrcode'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            $message = '捐款方式已删除';
        } else {
            $error = '删除失败';
        }
    } elseif (isset($_POST['toggle'])) {
        $id = intval($_POST['toggle']);
        $item = $donateModel->getById($id);
        if ($item) {
            $newStatus = $item['is_enabled'] ? 0 : 1;
            $donateModel->update($id, ['is_enabled' => $newStatus]);
            $message = $newStatus ? '已启用' : '已禁用';
        }
    }
}

// 获取编辑数据
$editData = null;
if ($action === 'edit') {
    $id = intval($_GET['id'] ?? 0);
    $editData = $donateModel->getById($id);
}

// 获取列表
$list = $donateModel->getList(false);
$qrcodeList = $donateModel->getByPayType('qrcode');
?>

<div class="max-w-4xl mx-auto">
    <?php if ($message): ?>
        <div class="glass rounded-xl p-4 mb-6 text-green-400">
            <?= e($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="glass rounded-xl p-4 mb-6 text-red-400">
            <?= e($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- 添加/编辑表单 -->
        <div class="glass rounded-xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4"><?= $editData ? '编辑' : '添加' ?>捐款方式</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                
                <!-- 平台名称 -->
                <div>
                    <label class="block mb-2">平台名称 *</label>
                    <input type="text" name="platform" value="<?= e($editData['platform'] ?? '') ?>" required
                           placeholder="如：微信支付、支付宝"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <!-- 支付类型 -->
                <div>
                    <label class="block mb-2">支付类型 *</label>
                    <select name="pay_type" id="pay_type" required
                            onchange="togglePayFields()"
                            class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                        <option value="qrcode" <?= ($editData['pay_type'] ?? '') === 'qrcode' ? 'selected' : '' ?>>收款二维码</option>
                        <option value="yipay" <?= ($editData['pay_type'] ?? '') === 'yipay' ? 'selected' : '' ?>>易支付</option>
                        <option value="mapay" <?= ($editData['pay_type'] ?? '') === 'mapay' ? 'selected' : '' ?>>码支付</option>
                        <option value="link" <?= ($editData['pay_type'] ?? '') === 'link' ? 'selected' : '' ?>>自定义链接</option>
                    </select>
                </div>
                
                <!-- 二维码上传（qrcode类型） -->
                <div id="qrcode_field" class="<?= ($editData['pay_type'] ?? 'qrcode') !== 'qrcode' ? 'hidden' : '' ?>">
                    <label class="block mb-2">收款二维码</label>
                    <?php if (!empty($editData['qrcode'])): ?>
                        <div class="mb-2">
                            <img src="<?= site_url($editData['qrcode']) ?>" alt="当前二维码" class="w-32 h-32 object-contain bg-white/10 rounded-lg">
                            <p class="text-sm opacity-60 mt-1">当前二维码</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="qrcode_file" accept="image/*"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                    <p class="text-sm opacity-60 mt-1">支持 JPG、PNG、GIF 格式</p>
                    <input type="hidden" name="qrcode" value="<?= e($editData['qrcode'] ?? '') ?>">
                </div>
                
                <!-- 链接输入（link类型） -->
                <div id="link_field" class="<?= ($editData['pay_type'] ?? '') !== 'link' ? 'hidden' : '' ?>">
                    <label class="block mb-2">跳转链接</label>
                    <input type="url" name="link" value="<?= e($editData['link'] ?? '') ?>"
                           placeholder="https://..."
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                    <p class="text-sm opacity-60 mt-1">填写你想跳转的链接地址</p>
                </div>
                
                <!-- 易支付/码支付说明 -->
                <div id="payment_info" class="<?= in_array(($editData['pay_type'] ?? ''), ['yipay', 'mapay']) ? '' : 'hidden' ?>">
                    <div class="glass-dark rounded-lg p-4">
                        <p class="text-sm">
                            <i class="fas fa-info-circle mr-1"></i>
                            易支付/码支付配置需要在 <a href="payment.php" class="text-blue-400 hover:underline">支付配置</a> 页面进行设置。
                            添加后，访客可以在前台选择支付金额并完成赞助。
                        </p>
                    </div>
                </div>
                
                <!-- 账户名称 -->
                <div>
                    <label class="block mb-2">账户名称/昵称</label>
                    <input type="text" name="account_name" value="<?= e($editData['account_name'] ?? '') ?>"
                           placeholder="可选"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <!-- 描述 -->
                <div>
                    <label class="block mb-2">描述文字</label>
                    <input type="text" name="description" value="<?= e($editData['description'] ?? '') ?>"
                           placeholder="可选，如：扫码打赏一杯咖啡"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <!-- 排序 -->
                <div>
                    <label class="block mb-2">排序（越小越靠前）</label>
                    <input type="number" name="sort_order" value="<?= $editData['sort_order'] ?? 0 ?>"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <!-- 启用 -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_enabled" value="1" <?= ($editData['is_enabled'] ?? 1) ? 'checked' : '' ?>>
                    <label>启用此捐款方式</label>
                </div>
                
                <!-- 按钮 -->
                <div class="flex space-x-2 pt-4">
                    <button type="submit" name="save" value="1" class="btn btn-primary">保存</button>
                    <a href="?" class="btn btn-ghost">取消</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- 列表 -->
        <div class="glass rounded-xl p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">捐款管理</h2>
                <a href="?action=add" class="btn btn-primary">添加捐款方式</a>
            </div>
            
            <?php if (empty($list)): ?>
                <p class="opacity-70">暂无捐款方式，点击上方按钮添加。</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($list as $item): ?>
                        <div class="glass-dark rounded-lg p-4 flex justify-between items-start">
                            <div class="flex gap-4">
                                <?php if ($item['pay_type'] === 'qrcode' && $item['qrcode']): ?>
                                    <img src="<?= site_url($item['qrcode']) ?>" alt="<?= e($item['platform']) ?>" 
                                         class="w-20 h-20 object-contain bg-white/10 rounded-lg">
                                <?php elseif ($item['pay_type'] === 'yipay'): ?>
                                    <div class="w-20 h-20 bg-blue-500/20 rounded-lg flex items-center justify-center text-3xl">💳</div>
                                <?php elseif ($item['pay_type'] === 'mapay'): ?>
                                    <div class="w-20 h-20 bg-green-500/20 rounded-lg flex items-center justify-center text-3xl">💚</div>
                                <?php elseif ($item['pay_type'] === 'link'): ?>
                                    <div class="w-20 h-20 bg-purple-500/20 rounded-lg flex items-center justify-center text-3xl">🔗</div>
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-white/10 rounded-lg flex items-center justify-center text-3xl">❓</div>
                                <?php endif; ?>
                                
                                <div>
                                    <h3 class="font-bold">
                                        <?= e($item['platform']) ?>
                                        <?php if (!$item['is_enabled']): ?>
                                            <span class="text-xs bg-red-500/30 px-2 py-0.5 rounded ml-2">已禁用</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-sm opacity-70">
                                        类型：<?= Donate::getPayTypeName($item['pay_type']) ?>
                                    </p>
                                    <?php if ($item['account_name']): ?>
                                        <p class="text-sm opacity-70">账户：<?= e($item['account_name']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($item['description']): ?>
                                        <p class="text-sm opacity-50"><?= e($item['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <form method="post" class="inline">
                                    <button type="submit" name="toggle" value="<?= $item['id'] ?>" 
                                            class="btn btn-ghost btn-sm">
                                        <?= $item['is_enabled'] ? '禁用' : '启用' ?>
                                    </button>
                                </form>
                                <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-ghost btn-sm">编辑</a>
                                <form method="post" class="inline" onsubmit="return confirm('确定删除吗？');">
                                    <button type="submit" name="delete" value="<?= $item['id'] ?>" class="btn btn-danger btn-sm">删除</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 帮助说明 -->
        <div class="glass rounded-xl p-6">
            <h3 class="text-lg font-bold mb-3">支付类型说明</h3>
            <div class="space-y-3 text-sm">
                <div class="flex gap-3">
                    <span class="bg-white/10 px-2 py-1 rounded">收款二维码</span>
                    <span class="opacity-70">上传静态收款码图片，适合个人微信/支付宝收款</span>
                </div>
                <div class="flex gap-3">
                    <span class="bg-blue-500/20 px-2 py-1 rounded">易支付</span>
                    <span class="opacity-70">接入第三方易支付平台，支持支付宝/微信自动回调</span>
                </div>
                <div class="flex gap-3">
                    <span class="bg-green-500/20 px-2 py-1 rounded">码支付</span>
                    <span class="opacity-70">接入码支付平台，支持多种支付方式</span>
                </div>
                <div class="flex gap-3">
                    <span class="bg-purple-500/20 px-2 py-1 rounded">自定义链接</span>
                    <span class="opacity-70">跳转到指定链接，可用于爱发电等第三方平台</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function togglePayFields() {
    var payType = document.getElementById('pay_type').value;
    var qrcodeField = document.getElementById('qrcode_field');
    var linkField = document.getElementById('link_field');
    var paymentInfo = document.getElementById('payment_info');
    
    qrcodeField.classList.add('hidden');
    linkField.classList.add('hidden');
    paymentInfo.classList.add('hidden');
    
    if (payType === 'qrcode') {
        qrcodeField.classList.remove('hidden');
    } else if (payType === 'link') {
        linkField.classList.remove('hidden');
    } else if (payType === 'yipay' || payType === 'mapay') {
        paymentInfo.classList.remove('hidden');
    }
}
</script>

<?php admin_footer(); ?>
