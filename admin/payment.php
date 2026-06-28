<?php
/**
 * MyGlassBlog PHP - 支付网关配置
 */
require_once __DIR__ . '/common.php';
admin_header('支付配置');

$paymentModel = new PaymentGateway();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save'])) {
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'gateway_type' => trim($_POST['gateway_type']),
            'gateway_name' => trim($_POST['gateway_name']),
            'pid' => trim($_POST['pid']),
            'key' => trim($_POST['key']),
            'api_url' => trim($_POST['api_url']),
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
        ];
        
        if (empty($data['gateway_type']) || empty($data['pid']) || empty($data['key'])) {
            $error = '网关类型、PID和密钥不能为空';
        } else {
            if ($id > 0) {
                if ($paymentModel->update($id, $data)) {
                    $message = '支付网关配置已更新';
                } else {
                    $error = '更新失败';
                }
            } else {
                if ($paymentModel->create($data)) {
                    $message = '支付网关配置已添加';
                } else {
                    $error = '添加失败';
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        if ($paymentModel->delete($id)) {
            $message = '支付网关配置已删除';
        } else {
            $error = '删除失败';
        }
    } elseif (isset($_POST['test'])) {
        $gateway_type = trim($_POST['gateway_type']);
        $config = $paymentModel->getByType($gateway_type);
        
        if ($config) {
            if ($gateway_type === 'yipay') {
                $pay = new YiPayment($config['pid'], $config['key'], $config['api_url']);
                // 测试查询订单接口
                $result = $pay->queryOrder('test_order');
                if ($result && isset($result['code'])) {
                    $message = '易支付接口连接正常（测试查询返回：' . $result['msg'] . '）';
                } else {
                    $error = '易支付接口连接失败';
                }
            } elseif ($gateway_type === 'mapay') {
                $pay = new MaPayment($config['pid'], $config['key'], $config['api_url']);
                $result = $pay->queryOrder('test_order');
                if ($result && isset($result['code'])) {
                    $message = '码支付接口连接正常（测试查询返回：' . $result['msg'] . '）';
                } else {
                    $error = '码支付接口连接失败';
                }
            }
        } else {
            $error = '未找到网关配置';
        }
    }
}

// 获取编辑数据
$editData = null;
if ($action === 'edit') {
    $id = intval($_GET['id'] ?? 0);
    $editData = $paymentModel->getById($id);
}

// 获取列表
$list = $paymentModel->getList();
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
            <h2 class="text-2xl font-bold mb-4"><?= $editData ? '编辑支付网关' : '添加支付网关' ?></h2>
            <form method="post" class="space-y-4">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block mb-2">网关类型</label>
                    <select name="gateway_type" required class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                        <option value="yipay" <?= ($editData['gateway_type'] ?? '') === 'yipay' ? 'selected' : '' ?>>易支付 (YiPay)</option>
                        <option value="mapay" <?= ($editData['gateway_type'] ?? '') === 'mapay' ? 'selected' : '' ?>>码支付 (MaPay)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block mb-2">网关名称</label>
                    <input type="text" name="gateway_name" value="<?= e($editData['gateway_name'] ?? '') ?>" 
                           placeholder="如：易支付-支付宝" required
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <div>
                    <label class="block mb-2">商户PID</label>
                    <input type="text" name="pid" value="<?= e($editData['pid'] ?? '') ?>" 
                           placeholder="商户ID" required
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <div>
                    <label class="block mb-2">商户密钥</label>
                    <input type="password" name="key" value="<?= e($editData['key'] ?? '') ?>" 
                           placeholder="商户密钥" required
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <div>
                    <label class="block mb-2">API地址</label>
                    <input type="url" name="api_url" value="<?= e($editData['api_url'] ?? '') ?>" 
                           placeholder="https://pay.example.com" required
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40">
                </div>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_enabled" value="1" <?= ($editData['is_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <label>启用此网关</label>
                </div>
                
                <div class="flex space-x-2">
                    <button type="submit" name="save" value="1" class="btn btn-primary">保存</button>
                    <a href="?action=list" class="btn btn-ghost">取消</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- 列表 -->
        <div class="glass rounded-xl p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">支付网关配置</h2>
                <a href="?action=add" class="btn btn-primary">添加网关</a>
            </div>
            
            <?php if (empty($list)): ?>
                <p class="opacity-70">暂无支付网关配置，请点击"添加网关"进行配置。</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($list as $item): ?>
                        <div class="glass-dark rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold"><?= e($item['gateway_name']) ?></h3>
                                <p class="text-sm opacity-70">类型：<?= $item['gateway_type'] === 'yipay' ? '易支付' : '码支付' ?></p>
                                <p class="text-sm opacity-70">PID：<?= e($item['pid']) ?></p>
                                <p class="text-sm opacity-70">状态：<?= $item['is_enabled'] ? '<span class="text-green-400">已启用</span>' : '<span class="text-red-400">未启用</span>' ?></p>
                            </div>
                            <div class="flex space-x-2">
                                <form method="post" class="inline">
                                    <input type="hidden" name="gateway_type" value="<?= $item['gateway_type'] ?>">
                                    <button type="submit" name="test" value="1" class="btn btn-ghost btn-sm">测试</button>
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
        
        <!-- 使用说明 -->
        <div class="glass rounded-xl p-6">
            <h3 class="text-xl font-bold mb-4">使用说明</h3>
            <div class="space-y-2 text-sm opacity-80">
                <p><strong>易支付：</strong>填写你的易支付商户PID、密钥和API地址（如：https://pay.example.com）</p>
                <p><strong>码支付：</strong>填写你的码支付商户PID、密钥和API地址（默认：https://api.maepay.com）</p>
                <p><strong>异步回调：</strong>系统会自动生成回调地址，请在支付平台后台配置</p>
                <p><strong>测试连接：</strong>配置完成后可以点击"测试"按钮验证接口连通性</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/common.php'; ?>
