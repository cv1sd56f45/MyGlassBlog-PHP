<?php
/**
 * MyGlassBlog PHP - 支付完成跳转页面
 */
require_once __DIR__ . '/../includes/functions.php';

$order_no = $_GET['out_trade_no'] ?? '';
$order = null;
$message = '';
$error = '';

if (empty($order_no)) {
    $error = '订单号不存在';
} else {
    $orderModel = new PaymentOrder();
    $order = $orderModel->getByOrderNo($order_no);
    
    if (!$order) {
        $error = '订单不存在';
    } else {
        if ($order['status'] == 1) {
            $message = '支付成功！感谢你的赞助 ❤️';
        } elseif ($order['status'] == 0) {
            // 可能还在处理中，尝试查询订单状态
            $gatewayModel = new PaymentGateway();
            $gatewayConfig = $gatewayModel->getByType($order['gateway_type']);
            
            if ($gatewayConfig) {
                if ($order['gateway_type'] === 'yipay') {
                    $pay = new YiPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
                    $result = $pay->queryOrder($order_no);
                    
                    if ($result && $result['code'] === 1 && ($result['status'] === 1 || $result['state'] === 'SUCCESS')) {
                        $orderModel->updateStatus($order_no, 1, $result['trade_no'] ?? '', $result['money'] ?? $order['amount']);
                        $order = $orderModel->getByOrderNo($order_no); // 刷新订单信息
                        $message = '支付成功！感谢你的赞助 ❤️';
                    } else {
                        $error = '支付处理中，请稍后查看订单状态';
                    }
                } elseif ($order['gateway_type'] === 'mapay') {
                    $pay = new MaPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
                    $result = $pay->queryOrder($order_no);
                    
                    if ($result && $result['code'] === 1 && ($result['status'] === 1 || $result['state'] === 'SUCCESS')) {
                        $orderModel->updateStatus($order_no, 1, $result['trade_no'] ?? '', $result['money'] ?? $order['amount']);
                        $order = $orderModel->getByOrderNo($order_no);
                        $message = '支付成功！感谢你的赞助 ❤️';
                    } else {
                        $error = '支付处理中，请稍后查看订单状态';
                    }
                }
            } else {
                $error = '支付处理中，请稍后查看订单状态';
            }
        } else {
            $error = '支付失败或已取消';
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<h1 class="text-3xl font-bold mb-4 text-center">支付结果</h1>

<div class="glass rounded-xl p-6 max-w-2xl mx-auto">
    <?php if ($message): ?>
        <div class="text-center mb-6">
            <div class="text-6xl mb-4">✅</div>
            <h2 class="text-2xl font-bold text-green-400 mb-2"><?= e($message) ?></h2>
        </div>
        
        <?php if ($order): ?>
            <div class="glass-dark rounded-lg p-4 space-y-2">
                <p><strong>订单号：</strong><?= e($order['order_no']) ?></p>
                <p><strong>支付金额：</strong>￥<?= number_format($order['amount'], 2) ?></p>
                <p><strong>支付时间：</strong><?= e($order['pay_time']) ?></p>
                <?php if ($order['payer_name']): ?>
                    <p><strong>赞助人：</strong><?= e($order['payer_name']) ?></p>
                <?php endif; ?>
                <?php if ($order['message']): ?>
                    <p><strong>留言：</strong><?= e($order['message']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-6">
            <a href="<?= site_url('donate.php') ?>" class="btn btn-primary">返回赞赏页面</a>
        </div>
        
    <?php elseif ($error): ?>
        <div class="text-center mb-6">
            <div class="text-6xl mb-4">❌</div>
            <h2 class="text-2xl font-bold text-red-400 mb-2"><?= e($error) ?></h2>
        </div>
        
        <?php if ($order): ?>
            <div class="glass-dark rounded-lg p-4 space-y-2">
                <p><strong>订单号：</strong><?= e($order['order_no']) ?></p>
                <p><strong>订单状态：</strong>
                    <?php if ($order['status'] == 0): ?>
                        待支付
                    <?php elseif ($order['status'] == 1): ?>
                        <span class="text-green-400">已支付</span>
                    <?php elseif ($order['status'] == 2): ?>
                        <span class="text-red-400">已取消</span>
                    <?php elseif ($order['status'] == 3): ?>
                        <span class="text-red-400">支付失败</span>
                    <?php endif; ?>
                </p>
                <p><strong>创建时间：</strong><?= e($order['created_at']) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-6">
            <a href="<?= site_url('payment.php') ?>" class="btn btn-primary">重新支付</a>
            <a href="<?= site_url('donate.php') ?>" class="btn btn-ghost ml-2">返回赞赏页面</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
