<?php
/**
 * MyGlassBlog PHP - 捐款/赞赏页面
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$donateModel = new Donate();
$donations = $donateModel->getList(true);

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-4 text-center">☕ 赞赏支持</h1>

<div class="glass rounded-xl p-6 mb-8 text-center opacity-80 max-w-2xl mx-auto">
    <p class="text-lg mb-2">如果我的内容对你有所帮助，欢迎赞赏支持 ❤️</p>
    <p class="text-sm opacity-70">你的支持是我持续创作的动力！</p>
</div>

<!-- 在线支付按钮 -->
<?php 
$paymentGatewayModel = new PaymentGateway();
$enabledGateways = $paymentGatewayModel->getEnabled();
?>
<?php if (!empty($enabledGateways)): ?>
    <div class="text-center mb-8">
        <a href="<?= site_url('payment.php') ?>" class="btn btn-primary btn-lg">💳 在线赞助</a>
        <p class="text-sm opacity-60 mt-2">支持支付宝、微信支付等多种方式</p>
    </div>
<?php endif; ?>

<?php if (empty($donations)): ?>
    <div class="glass rounded-xl p-12 text-center opacity-70 max-w-md mx-auto">
        <p class="text-2xl mb-2">🙏</p>
        <p>暂无赞赏方式，欢迎通过其他方式联系我</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?= min(count($donations), 4) ?> gap-6 max-w-4xl mx-auto">
        <?php foreach ($donations as $d): ?>
            <div class="glass rounded-xl p-6 flex flex-col items-center text-center hover:bg-white/15 transition-colors group">
                <?php if ($d['qrcode']): ?>
                    <div class="w-48 h-48 rounded-xl overflow-hidden mb-4 shadow-lg bg-white/10 flex items-center justify-center">
                        <img src="<?= e(site_url(ltrim($d['qrcode'], '/'))) ?>" 
                             alt="<?= e($d['platform']) ?>收款码"
                             class="w-full h-full object-contain p-2"
                             loading="lazy">
                    </div>
                <?php else: ?>
                    <div class="w-48 h-48 rounded-xl mb-4 bg-white/10 flex items-center justify-center">
                        <span class="text-6xl opacity-40"><?= $d['platform'] === '微信支付' ? '💬' : ($d['platform'] === '支付宝' ? '🔵' : '💳') ?></span>
                    </div>
                <?php endif; ?>
                
                <h3 class="text-lg font-bold mb-1"><?= e($d['platform']) ?></h3>
                
                <?php if ($d['account_name']): ?>
                    <p class="text-sm opacity-70 mb-1"><?= e($d['account_name']) ?></p>
                <?php endif; ?>
                
                <?php if ($d['description']): ?>
                    <p class="text-xs opacity-50"><?= e($d['description']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
