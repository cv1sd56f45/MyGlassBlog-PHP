<?php
/**
 * MyGlassBlog PHP - 赞赏支持页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$donateModel = new Donate();
$paymentGatewayModel = new PaymentGateway();

// 获取所有启用的捐款方式
$allDonations = $donateModel->getList(true);
$qrcodeDonations = $donateModel->getByPayType('qrcode');
$linkDonations = $donateModel->getByPayType('link');
$enabledGateways = $paymentGatewayModel->getEnabled();

// 统计
$stats = $donateModel->getCount();

// 获取站点配置
$icpNumber = $settings->get('icp_number', '');
$siteName = $settings->get('site_name', 'MyGlassBlog');

$message = '';
$error = '';

// 处理支付请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pay'])) {
    $amount = floatval($_POST['amount']);
    $pay_type = trim($_POST['pay_type']);
    $payer_name = trim($_POST['payer_name'] ?? '');
    $payer_contact = trim($_POST['payer_contact'] ?? '');
    $donate_message = trim($_POST['message'] ?? '');
    
    if ($amount <= 0) {
        $error = '请输入有效的赞助金额';
    } elseif (empty($pay_type)) {
        $error = '请选择支付方式';
    } else {
        // 创建订单
        $orderModel = new PaymentOrder();
        $order_no = $orderModel->generateOrderNo();
        
        $orderData = [
            'order_no' => $order_no,
            'gateway_type' => $pay_type,
            'pay_type' => $pay_type,
            'amount' => $amount,
            'payer_name' => $payer_name,
            'payer_contact' => $payer_contact,
            'message' => $donate_message,
        ];
        
        $orderModel->create($orderData);
        
        // 调用支付接口
        $notify_url = site_url('payment/notify.php');
        $return_url = site_url('payment/return.php');
        
        $params = [
            'out_trade_no' => $order_no,
            'subject' => '赞助 - ' . ($payer_name ?: '匿名'),
            'money' => $amount,
            'type' => $pay_type,
            'notify_url' => $notify_url,
            'return_url' => $return_url,
        ];
        
        if ($pay_type === 'yipay') {
            $gatewayConfig = $paymentGatewayModel->getByType('yipay');
            if ($gatewayConfig) {
                $pay = new YiPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
                $result = $pay->apiPay($params);
                
                if ($result && $result['code'] === 1) {
                    header('Location: ' . $result['payurl']);
                    exit;
                } else {
                    $error = '支付请求失败：' . ($result['msg'] ?? '未知错误');
                }
            } else {
                $error = '未配置易支付网关';
            }
        } elseif ($pay_type === 'mapay') {
            $gatewayConfig = $paymentGatewayModel->getByType('mapay');
            if ($gatewayConfig) {
                $pay = new MaPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
                $result = $pay->apiPay($params);
                
                if ($result && $result['code'] === 1) {
                    header('Location: ' . $result['payurl']);
                    exit;
                } else {
                    $error = '支付请求失败：' . ($result['msg'] ?? '未知错误');
                }
            } else {
                $error = '未配置码支付网关';
            }
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<style>
.donate-tab {
    @apply px-4 py-2 rounded-lg transition-all cursor-pointer;
    @apply bg-white/10 hover:bg-white/15;
}
.donate-tab.active {
    @apply bg-white/20;
}
</style>

<h1 class="text-3xl font-bold mb-4 text-center">☕ 赞赏支持</h1>

<div class="glass rounded-xl p-6 mb-8 text-center opacity-80 max-w-2xl mx-auto">
    <p class="text-lg mb-2">如果我的内容对你有所帮助，欢迎赞赏支持 ❤️</p>
    <p class="text-sm opacity-70">你的支持是我持续创作的动力！</p>
</div>

<?php if (!empty($error)): ?>
    <div class="glass rounded-xl p-4 mb-6 text-red-400 max-w-2xl mx-auto">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($allDonations)): ?>
    <!-- 支付类型切换 -->
    <div class="flex justify-center gap-2 mb-8">
        <?php if (!empty($qrcodeDonations)): ?>
            <button onclick="switchTab('qrcode')" id="tab-qrcode" class="donate-tab active">📱 扫码打赏</button>
        <?php endif; ?>
        <?php if (!empty($linkDonations)): ?>
            <button onclick="switchTab('link')" id="tab-link" class="donate-tab">🔗 链接赞助</button>
        <?php endif; ?>
        <?php if (!empty($enabledGateways)): ?>
            <button onclick="switchTab('online')" id="tab-online" class="donate-tab">💳 在线支付</button>
        <?php endif; ?>
    </div>

    <!-- 二维码打赏 -->
    <?php if (!empty($qrcodeDonations)): ?>
        <div id="content-qrcode" class="donate-content max-w-4xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($qrcodeDonations as $item): ?>
                    <div class="glass rounded-xl p-4 text-center hover:bg-white/15 transition-all">
                        <img src="<?= site_url($item['qrcode']) ?>" alt="<?= e($item['platform']) ?>" 
                             class="w-full aspect-square object-contain rounded-lg mb-3 bg-white/10">
                        <h3 class="font-bold"><?= e($item['platform']) ?></h3>
                        <?php if ($item['description']): ?>
                            <p class="text-sm opacity-60 mt-1"><?= e($item['description']) ?></p>
                        <?php endif; ?>
                        <?php if ($item['account_name']): ?>
                            <p class="text-xs opacity-40 mt-1"><?= e($item['account_name']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 链接赞助 -->
    <?php if (!empty($linkDonations)): ?>
        <div id="content-link" class="donate-content max-w-2xl mx-auto hidden">
            <div class="space-y-4">
                <?php foreach ($linkDonations as $item): ?>
                    <a href="<?= e($item['link']) ?>" target="_blank" rel="noopener noreferrer"
                       class="glass rounded-xl p-6 flex items-center gap-4 hover:bg-white/15 transition-all block">
                        <?php if ($item['qrcode']): ?>
                            <img src="<?= site_url($item['qrcode']) ?>" alt="<?= e($item['platform']) ?>" 
                                 class="w-16 h-16 object-contain rounded-lg bg-white/10">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-purple-500/20 rounded-lg flex items-center justify-center text-2xl">🔗</div>
                        <?php endif; ?>
                        <div class="flex-1">
                            <h3 class="font-bold text-lg"><?= e($item['platform']) ?></h3>
                            <?php if ($item['description']): ?>
                                <p class="text-sm opacity-60"><?= e($item['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-2xl">→</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 在线支付 -->
    <?php if (!empty($enabledGateways)): ?>
        <div id="content-online" class="donate-content max-w-2xl mx-auto hidden">
            <div class="glass rounded-xl p-6">
                <h2 class="text-xl font-bold mb-4 text-center">💳 在线赞助</h2>
                
                <form method="post" class="space-y-4">
                    <!-- 赞助金额 -->
                    <div>
                        <label class="block mb-2 font-bold">赞助金额（元）</label>
                        <input type="number" name="amount" min="0.01" step="0.01" required
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40"
                               placeholder="请输入金额">
                        <div class="flex space-x-2 mt-2">
                            <button type="button" onclick="setAmount(1)" class="btn btn-ghost btn-sm">1元</button>
                            <button type="button" onclick="setAmount(5)" class="btn btn-ghost btn-sm">5元</button>
                            <button type="button" onclick="setAmount(10)" class="btn btn-ghost btn-sm">10元</button>
                            <button type="button" onclick="setAmount(50)" class="btn btn-ghost btn-sm">50元</button>
                            <button type="button" onclick="setAmount(100)" class="btn btn-ghost btn-sm">100元</button>
                        </div>
                    </div>
                    
                    <!-- 支付方式 -->
                    <div>
                        <label class="block mb-2 font-bold">支付方式</label>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach ($enabledGateways as $gw): ?>
                                <?php if ($gw['gateway_type'] === 'yipay'): ?>
                                    <label class="glass-dark rounded-lg p-4 flex items-center space-x-2 cursor-pointer hover:bg-white/15">
                                        <input type="radio" name="pay_type" value="yipay" required>
                                        <span>💳 易支付</span>
                                    </label>
                                <?php elseif ($gw['gateway_type'] === 'mapay'): ?>
                                    <label class="glass-dark rounded-lg p-4 flex items-center space-x-2 cursor-pointer hover:bg-white/15">
                                        <input type="radio" name="pay_type" value="mapay" required>
                                        <span>💚 码支付</span>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 赞助人信息 -->
                    <div>
                        <label class="block mb-2">您的称呼（选填）</label>
                        <input type="text" name="payer_name" 
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40"
                               placeholder="匿名">
                    </div>
                    
                    <div>
                        <label class="block mb-2">联系方式（选填）</label>
                        <input type="text" name="payer_contact" 
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40"
                               placeholder="邮箱/QQ/微信">
                    </div>
                    
                    <div>
                        <label class="block mb-2">留言（选填）</label>
                        <textarea name="message" rows="2"
                                  class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40"
                                  placeholder="说点什么吧..."></textarea>
                    </div>
                    
                    <button type="submit" name="submit_pay" value="1" class="btn btn-primary btn-lg w-full">
                        立即支付
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="glass rounded-xl p-12 text-center opacity-70 max-w-md mx-auto">
        <p class="text-4xl mb-4">🙏</p>
        <p>暂未开启赞助功能</p>
    </div>
<?php endif; ?>

<script>
function switchTab(tab) {
    // 隐藏所有内容
    document.querySelectorAll('.donate-content').forEach(el => el.classList.add('hidden'));
    // 移除所有tab的active
    document.querySelectorAll('.donate-tab').forEach(el => el.classList.remove('active'));
    
    // 显示选中的内容
    const content = document.getElementById('content-' + tab);
    const tabBtn = document.getElementById('tab-' + tab);
    
    if (content) content.classList.remove('hidden');
    if (tabBtn) tabBtn.classList.add('active');
}

function setAmount(amount) {
    document.querySelector('input[name="amount"]').value = amount;
}

// 默认显示第一个tab
document.addEventListener('DOMContentLoaded', function() {
    const firstTab = document.querySelector('.donate-tab');
    if (firstTab) {
        const tabId = firstTab.id.replace('tab-', '');
        switchTab(tabId);
    }
});
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
