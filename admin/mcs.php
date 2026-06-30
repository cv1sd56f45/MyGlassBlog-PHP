<?php
/**
 * MyGlassBlog PHP - MC服务器状态管理
 */
require_once __DIR__ . '/common.php';
admin_header('MC服务器');

$motd = new MotdApi();
$message = '';
$error = '';

// 清除缓存
if (isset($_POST['clear_cache'])) {
    $motd->clearCache();
    $message = '缓存已清除';
}

// 如果提交查询，查询服务器
$serverData = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ip'])) {
    $ip = trim($_POST['ip']);
    $port = trim($_POST['port'] ?? '');
    $stype = trim($_POST['stype'] ?? 'java');
    $srv = isset($_POST['srv']);
    
    $serverData = $motd->query($ip, $port ? intval($port) : null, $stype, $srv);
}

?>

<div class="max-w-4xl mx-auto">
    <?php if ($message): ?>
        <div class="glass rounded-xl p-4 mb-6 text-green-400"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="glass rounded-xl p-4 mb-6 text-red-400"><?= e($error) ?></div>
    <?php endif; ?>
    
    <!-- 查询表单 -->
    <div class="glass rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">🎮 MC服务器状态查询</h2>
        <form method="post" class="space-y-3">
            <div class="flex gap-3">
                <div class="flex-1">
                    <label class="block mb-1 text-sm">服务器地址</label>
                    <input type="text" name="ip" required
                           placeholder="mc.hypixel.net"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20">
                </div>
                <div class="w-24">
                    <label class="block mb-1 text-sm">端口</label>
                    <input type="number" name="port" placeholder="25565"
                           class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20">
                </div>
                <div class="w-32">
                    <label class="block mb-1 text-sm">版本</label>
                    <select name="stype" class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20">
                        <option value="java">Java版</option>
                        <option value="bedrock">基岩版</option>
                        <option value="auto">自动检测</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 items-center">
                <label class="flex items-center gap-1 text-sm">
                    <input type="checkbox" name="srv" value="1"> SRV解析
                </label>
                <button type="submit" class="btn btn-primary btn-sm">查询</button>
            </div>
        </form>
    </div>
    
    <?php if ($serverData): ?>
        <!-- 查询结果 -->
        <div class="glass rounded-xl p-6 mb-6">
            <h2 class="text-xl font-bold mb-3">查询结果</h2>
            
            <div class="bg-white/10 rounded-lg p-4 mb-4">
                <pre class="text-sm overflow-x-auto"><?php
                    echo htmlspecialchars(json_encode($serverData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                ?></pre>
            </div>
            
            <p class="text-sm opacity-60">
                状态: <strong><?= MotdApi::getStatusText($serverData['status']) ?></strong>
                | 地址: <?= e($serverData['host'] ?? 'N/A') ?>
            </p>
            
            <?php if ($serverData['status'] === 'online' && !empty($serverData['players'])): ?>
                <p class="text-sm opacity-60 mt-1">
                    玩家: <?= MotdApi::formatPlayers($serverData['players']['online'] ?? 0, $serverData['players']['max'] ?? 0) ?>
                    | 延迟: <?= e($serverData['delay'] ?? 'N/A') ?>ms
                    | 版本: <?= e($serverData['version'] ?? 'N/A') ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- 操作 -->
    <div class="glass rounded-xl p-6">
        <h2 class="text-xl font-bold mb-4">管理</h2>
        <div class="flex gap-3">
            <form method="post">
                <button type="submit" name="clear_cache" value="1" class="btn btn-ghost btn-sm"
                        onclick="return confirm('确定清除所有MOTD查询缓存？')">
                    清除缓存
                </button>
            </form>
            <a href="<?= site_url('mcs.php') ?>" target="_blank" class="btn btn-ghost btn-sm">
                查看前端
            </a>
        </div>
    </div>
    
    <!-- 说明 -->
    <div class="glass rounded-xl p-6 mt-6">
        <h3 class="text-lg font-bold mb-3">📖 说明</h3>
        <div class="text-sm opacity-70 space-y-2">
            <p>本功能使用 <a href="https://motd.minebbs.com" target="_blank" rel="nofollow" class="underline">MineBBS MOTD</a> API 查询 Minecraft 服务器状态。</p>
            <p>API 端点: <code>GET /api/status</code></p>
            <p>查询结果缓存 60 秒，避免频繁请求。</p>
            <p>可用参数: <code>ip</code> (必需), <code>port</code>, <code>stype</code> (java/bedrock/auto), <code>srv</code> (bool), <code>icon</code></p>
        </div>
    </div>
</div>

<?php admin_footer(); ?>
