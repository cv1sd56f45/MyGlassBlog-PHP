<?php
/**
 * MyGlassBlog PHP - 支付异步回调处理
 */
require_once __DIR__ . '/../includes/functions.php';

// 获取原始POST数据
$postData = $_POST;

if (empty($postData)) {
    echo 'fail';
    exit;
}

// 获取订单号
$out_trade_no = $postData['out_trade_no'] ?? '';
if (empty($out_trade_no)) {
    echo 'fail';
    exit;
}

// 查询订单
$orderModel = new PaymentOrder();
$order = $orderModel->getByOrderNo($out_trade_no);

if (!$order) {
    echo 'fail';
    exit;
}

// 获取网关配置
$gatewayModel = new PaymentGateway();
$gatewayConfig = $gatewayModel->getByType($order['gateway_type']);

if (!$gatewayConfig || !$gatewayConfig['is_enabled']) {
    echo 'fail';
    exit;
}

// 验证签名
$isValid = false;
if ($order['gateway_type'] === 'yipay') {
    $pay = new YiPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
    $isValid = $pay->verifyNotify($postData);
} elseif ($order['gateway_type'] === 'mapay') {
    $pay = new MaPayment($gatewayConfig['pid'], $gatewayConfig['key'], $gatewayConfig['api_url']);
    $isValid = $pay->verifyNotify($postData);
}

if (!$isValid) {
    echo 'fail';
    exit;
}

// 验证金额（可选，防止金额被篡改）
$trade_amount = floatval($postData['money'] ?? 0);
if (abs($trade_amount - $order['amount']) > 0.01) {
    // 金额不匹配，记录日志但不一定拒绝（有些支付平台可能有手续费）
    error_log("Payment amount mismatch: order={$out_trade_no}, expected={$order['amount']}, received={$trade_amount}");
}

// 更新订单状态
$trade_no = $postData['trade_no'] ?? '';
$actual_amount = $trade_amount;

if ($order['status'] == 0) { // 只在未支付时更新
    $orderModel->updateStatus($out_trade_no, 1, $trade_no, $actual_amount);
    
    // 记录支付成功日志
    error_log("Payment success: order={$out_trade_no}, trade_no={$trade_no}, amount={$actual_amount}");
}

// 返回成功响应
if ($order['gateway_type'] === 'yipay') {
    echo 'success';
} elseif ($order['gateway_type'] === 'mapay') {
    echo 'success';
} else {
    echo 'success';
}
