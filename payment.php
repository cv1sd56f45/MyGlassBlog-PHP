<?php
/**
 * MyGlassBlog PHP - 在线赞助页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$paymentGatewayModel = new PaymentGateway();
$enabledGateways = $paymentGatewayModel->getEnabled();

// 如果没有启用的支付网关，显示提示
if (empty($enabledGateways)) {
    require_once __DIR__ . '/templates/header.php';
    echo '<div class="glass rounded-xl p-12 text-center opacity-70 max-w-md mx-auto">';
    echo '<p class="text-2xl mb-2">🙏</p>';
    echo '<p>暂未开启在线赞助功能</p>';
    echo '</div>';
    require_once __DIR__ . '/templates/footer.php';
    exit;
}

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
            'gateway_type' => $enabledGateways[0]['gateway_type'], // 使用第一个启用的网关
            'pay_type' => $pay_type,
            'amount' => $amount,
            'payer_name' => $payer_name,
            'payer_contact' => $payer_contact,
            'message' => $donate_message,
        ];
        
        $orderModel->create($orderData);
        
        // 调用支付接口
        $gatewayConfig = $enabledGateways[0];
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
        
        if ($gatewayConfig['gateway_type'] === 'yipay') {
            $pay = new YiPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
            $result = $pay->apiPay($params);
            
            if ($result && $result['code'] === 1) {
                // 跳转至支付页面
                header('Location: ' . $result['payurl']);
                exit;
            } else {
                $error = '支付请求失败：' . ($result['msg'] ?? '未知错误');
            }
        } elseif ($gatewayConfig['gateway_type'] === 'mapay') {
            $pay = new MaPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
            $result = $pay->apiPay($params);
            
            if ($result && $result['code'] === 1) {
                header('Location: ' . $result['payurl']);
                exit;
            } else {
                $error = '支付请求失败：' . ($result['msg'] ?? '未知错误');
            }
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-4 text-center">💳 在线赞助</h1>

<div class="glass rounded-xl p-6 mb-8 max-w-2xl mx-auto">
    <?php if ($message): ?>
        <div class="text-green-400 mb-4"><?= e($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="text-red-400 mb-4"><?= e($error) ?></div>
    <?php endif; ?>
    
    <form method="post" class="space-y-6">
        <!-- 赞助金额 -->
        <div>
            <label class="block mb-2 font-bold">赞助金额（元）*</label>
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
            <label class="block mb-2 font-bold">支付方式*</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="glass-dark rounded-lg p-4 flex items-center space-x-2 cursor-pointer hover:bg-white/15">
                    <input type="radio" name="pay_type" value="alipay" required>
                    <span>💙 支付宝</span>
                </label>
                <label class="glass-dark rounded-lg p-4 flex items-center space-x-2 cursor-pointer hover:bg-white/15">
                    <input type="radio" name="pay_type" value="wxpay" required>
                    <span>💚 微信支付</span>
                </label>
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
            <textarea name="message" rows="3"
                      class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40"
                      placeholder="说点什么吧..."></textarea>
        </div>
        
        <button type="submit" name="submit_pay" value="1" class="btn btn-primary btn-lg w-full">
            立即支付
        </button>
    </form>
</div>

<script>
function setAmount(amount) {
    document.querySelector('input[name="amount"]').value = amount;
}
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
