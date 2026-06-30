<?php
/**
 * MyGlassBlog PHP - Minecraft 服务器状态查询
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$motd = new MotdApi();

$message = '';
$error = '';
$serverData = null;

// 处理查询
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ip'])) {
    $ip = trim($_POST['ip'] ?? $_GET['ip'] ?? '');
    $port = trim($_POST['port'] ?? '');
    $stype = trim($_POST['stype'] ?? $_GET['stype'] ?? 'java');
    $srv = isset($_POST['srv']) || isset($_GET['srv']);
    
    if (empty($ip)) {
        $error = '请输入服务器地址';
    } else {
        $serverData = $motd->query($ip, $port ? intval($port) : null, $stype, $srv);
        if ($serverData['status'] === 'online') {
            $message = '查询成功！';
        } elseif ($serverData['status'] === 'offline') {
            $error = $serverData['error'] ?? '服务器离线或地址不正确';
        } else {
            $error = $serverData['error'] ?? '查询失败';
        }
    }
}

// 示例服务器列表
$exampleServers = [
    ['ip' => 'mc.hypixel.net', 'name' => 'Hypixel'],
    ['ip' => 'play.2b2t.org', 'name' => '2b2t'],
    ['ip' => 'mc.weimc.bond', 'name' => ?&gt;<?= e($settings->get('site_title', 'My GlassBlog')) ?> 服务器'],
];

require_once __DIR__ . '/templates/header.php';
?>

<style>
.server-card {
    @apply glass rounded-xl p-6 transition-all;
}
.server-card.online {
    border-left: 4px solid #4ade80;
}
.server-card.offline {
    border-left: 4px solid #f87171;
}
.server-card.error {
    border-left: 4px solid #facc15;
}

.server-icon {
    width: 64px;
    height: 64px;
    image-rendering: pixelated;
}
.motd-display {
    font-family: 'Courier New', monospace;
    white-space: pre-wrap;
    word-break: break-all;
}

.progress-bar {
    height: 6px;
    border-radius: 3px;
    background: rgba(255,255,255,0.1);
    overflow: hidden;
}
.progress-bar .fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #4ade80, #22d3ee);
    transition: width 0.5s ease;
}

.param-input {
    @apply w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 focus:outline-none focus:border-white/40;
}
.btn-example {
    @apply px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition-all text-sm cursor-pointer;
}
</style>

<h1 class="text-3xl font-bold mb-4 text-center">🎮 Minecraft 服务器状态</h1>

<div class="glass rounded-xl p-6 mb-8 text-center opacity-80 max-w-2xl mx-auto">
    <p class="text-lg mb-2">查询 Minecraft 服务器的在线状态、玩家数量、延迟等信息</p>
    <p class="text-sm opacity-60">数据来自 <a href="https://motd.minebbs.com" target="_blank" rel="nofollow" class="underline">MineBBS MOTD</a></p>
</div>

<!-- 查询表单 -->
<div class="glass rounded-xl p-6 mb-8 max-w-2xl mx-auto">
    <form method="post" class="space-y-4">
        <div class="flex gap-2 items-end">
            <div class="flex-1">
                <label class="block text-sm mb-1">服务器地址 *</label>
                <input type="text" name="ip" value="<?= e($_POST['ip'] ?? $_GET['ip'] ?? '') ?>" required
                       placeholder="mc.hypixel.net"
                       class="param-input">
            </div>
            <div class="w-24">
                <label class="block text-sm mb-1">端口</label>
                <input type="number" name="port" value="<?= e($_POST['port'] ?? $_GET['port'] ?? '') ?>"
                       placeholder="25565" min="1" max="65535"
                       class="param-input">
            </div>
            <button type="submit" class="btn btn-primary">查询</button>
        </div>
        
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label class="text-sm mr-2">版本类型</label>
                <select name="stype" class="param-input w-auto inline-block">
                    <option value="java" <?= ($_POST['stype'] ?? 'java') === 'java' ? 'selected' : '' ?>>☕ Java版</option>
                    <option value="bedrock" <?= ($_POST['stype'] ?? '') === 'bedrock' ? 'selected' : '' ?>>🪨 基岩版</option>
                    <option value="auto" <?= ($_POST['stype'] ?? '') === 'auto' ? 'selected' : '' ?>>🔄 自动检测</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="srv" value="1" <?= isset($_POST['srv']) || isset($_GET['srv']) ? 'checked' : '' ?>>
                SRV 解析
            </label>
        </div>
        
        <!-- 示例服务器 -->
        <div>
            <label class="text-sm opacity-60">快速查询：</label>
            <div class="flex flex-wrap gap-2 mt-1">
                <?php foreach ($exampleServers as $svr): ?>
                    <button type="submit" name="ip" value="<?= e($svr['ip']) ?>" 
                            onclick="this.form.submit()"
                            class="btn-example">
                        <?= e($svr['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>

<?php if ($message || $error): ?>
    <!-- 提示信息 -->
    <div class="max-w-4xl mx-auto mb-6">
        <?php if ($message): ?>
            <div class="glass rounded-xl p-4 text-green-400"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="glass rounded-xl p-4 text-red-400"><?= e($error) ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($serverData): ?>
    <!-- 查询结果 -->
    <div class="max-w-4xl mx-auto">
        <div class="server-card <?= $serverData['status'] ?> mb-6">
            <div class="flex items-start gap-6">
                <!-- 服务器图标 -->
                <div class="flex-shrink-0">
                    <?php if (!empty($serverData['icon'])): ?>
                        <img src="<?= e($serverData['icon']) ?>" alt="服务器图标"
                             class="server-icon rounded-lg bg-white/10">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-white/10 rounded-lg flex items-center justify-center text-3xl">
                            <?= $serverData['status'] === 'online' ? '🟢' : '🔴' ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- 基本信息 -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold truncate"><?= e($serverData['host']) ?></h2>
                        <span class="text-sm <?= MotdApi::getStatusClass($serverData['status']) ?>">
                            ● <?= MotdApi::getStatusText($serverData['status']) ?>
                        </span>
                        <?php if (!empty($serverData['type'])): ?>
                            <span class="text-sm opacity-60"><?= MotdApi::getTypeIcon($serverData['type']) ?> <?= e($serverData['type']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($serverData['status'] === 'online'): ?>
                        <!-- MOTD -->
                        <?php if (!empty($serverData['motd_html'])): ?>
                            <div class="motd-display bg-black/20 rounded-lg p-3 mb-4 text-sm leading-relaxed">
                                <?= $serverData['motd_html'] ?>
                            </div>
                        <?php elseif (!empty($serverData['motd'])): ?>
                            <div class="motd-display bg-black/20 rounded-lg p-3 mb-4 text-sm">
                                <?= e($serverData['motd']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 数据网格 -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div class="glass-dark rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold">
                                    <?= e($serverData['players']['online'] ?? 0) ?>
                                </div>
                                <div class="text-xs opacity-60">在线玩家</div>
                            </div>
                            
                            <div class="glass-dark rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold opacity-70">
                                    <?= e($serverData['players']['max'] ?? 0) ?>
                                </div>
                                <div class="text-xs opacity-60">最大玩家</div>
                            </div>
                            
                            <div class="glass-dark rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold">
                                    <?= e($serverData['delay'] ?? 'N/A') ?>
                                </div>
                                <div class="text-xs opacity-60">延迟 (ms)</div>
                            </div>
                            
                            <div class="glass-dark rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold truncate text-sm">
                                    <?= e($serverData['version'] ?? 'N/A') ?>
                                </div>
                                <div class="text-xs opacity-60">版本</div>
                            </div>
                        </div>
                        
                        <!-- 玩家进度条 -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span>玩家占用</span>
                                <span><?= MotdApi::formatPlayers($serverData['players']['online'] ?? 0, $serverData['players']['max'] ?? 1) ?></span>
                            </div>
                            <?php 
                            $pct = ($serverData['players']['max'] ?? 1) > 0 
                                ? min(100, round(($serverData['players']['online'] ?? 0) / ($serverData['players']['max'] ?? 1) * 100)) 
                                : 0;
                            ?>
                            <div class="progress-bar">
                                <div class="fill" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                        
                        <!-- 详细信息 -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                            <?php if (!empty($serverData['ip'])): ?>
                                <div>
                                    <span class="opacity-50">IP:</span>
                                    <span><?= e($serverData['ip']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($serverData['port'])): ?>
                                <div>
                                    <span class="opacity-50">端口:</span>
                                    <span><?= e($serverData['port']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($serverData['protocol'])): ?>
                                <div>
                                    <span class="opacity-50">协议:</span>
                                    <span><?= e($serverData['protocol']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($serverData['gamemode'])): ?>
                                <div>
                                    <span class="opacity-50">游戏模式:</span>
                                    <span><?= e($serverData['gamemode']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($serverData['levelname'])): ?>
                                <div>
                                    <span class="opacity-50">存档名称:</span>
                                    <span><?= e($serverData['levelname']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 在线玩家列表 -->
                        <?php if (!empty($serverData['players']['sample'])): ?>
                            <details class="mt-4">
                                <summary class="text-sm opacity-70 cursor-pointer hover:opacity-100">
                                    在线列表 (<?= count($serverData['players']['sample']) ?> 人)
                                </summary>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <?php foreach ($serverData['players']['sample'] as $player): ?>
                                        <span class="px-2 py-1 bg-white/10 rounded text-sm">
                                            <?php if (!empty($player['name'])): ?>
                                                <?= e($player['name']) ?>
                                            <?php else: ?>
                                                未知
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                        
                        <!-- Mod列表 -->
                        <?php if (!empty($serverData['mod_info']['modList'])): ?>
                            <details class="mt-3">
                                <summary class="text-sm opacity-70 cursor-pointer hover:opacity-100">
                                    Mod 列表 (<?= count($serverData['mod_info']['modList']) ?> 个)
                                </summary>
                                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-1">
                                    <?php foreach ($serverData['mod_info']['modList'] as $mod): ?>
                                        <span class="text-xs px-2 py-1 bg-white/10 rounded">
                                            <?= e($mod['name'] ?? $mod['modid'] ?? '?') ?>
                                            <?php if (!empty($mod['version'])): ?> v<?= e($mod['version']) ?><?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                        
                        <!-- 缓存信息 -->
                        <?php if (!empty($serverData['_cached'])): ?>
                            <p class="mt-4 text-xs opacity-40">
                                缓存数据 (<?= e($serverData['_cache_time'] ?? '') ?>)
                            </p>
                        <?php endif; ?>
                        
                    <?php elseif ($serverData['status'] === 'offline'): ?>
                        <div class="glass-dark rounded-lg p-6 text-center">
                            <div class="text-6xl mb-4">💤</div>
                            <p class="text-lg opacity-70">服务器当前离线</p>
                            <p class="text-sm opacity-50 mt-2"><?= e($serverData['error'] ?? '') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 嵌入图片 -->
        <?php if (!empty($serverData['host'])): ?>
            <div class="glass rounded-xl p-6 mb-6">
                <h3 class="text-lg font-bold mb-3">📸 服务器状态图片</h3>
                <?php 
                $parts = explode(':', $serverData['host']);
                $imgIp = $parts[0];
                $imgPort = $parts[1] ?? null;
                $imgUrl = $motd->getImageUrl($imgIp, $imgPort, $stype);
                ?>
                <a href="<?= e($imgUrl) ?>" target="_blank" rel="nofollow">
                    <img src="<?= e($imgUrl) ?>" alt="MC服务器状态图" 
                         class="max-w-full rounded-lg mx-auto"
                         onerror="this.style.display='none'">
                </a>
                <p class="text-xs opacity-40 text-center mt-2">
                    <a href="<?= e($imgUrl) ?>" target="_blank" rel="nofollow" class="underline">查看原图</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
