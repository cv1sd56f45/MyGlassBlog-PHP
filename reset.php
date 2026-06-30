<?php
/**
 * MyGlassBlog PHP - 一键重置脚本
 *
 * 用途：删除 config.lock 并删除所有数据库表，方便重新执行 install.php
 * 使用：访问 reset.php → 看到确认提示 → 点击按钮执行
 *
 * ⚠️ 警告：此操作不可逆，所有数据将被删除
 */

$configPath = __DIR__ . '/includes/config.php';
$lockFile = __DIR__ . '/config.lock';
$installed = file_exists($lockFile);

// 处理重置请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    $deletedTables = [];
    $errors = [];

    // 1. 尝试连接数据库并删除所有表
    if (file_exists($configPath)) {
        try {
            $config = require $configPath;
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['db_host'],
                $config['db_port'],
                $config['db_name'],
                $config['db_charset'] ?? 'utf8mb4'
            );
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $t) {
                $pdo->exec("DROP TABLE IF EXISTS `$t`");
                $deletedTables[] = $t;
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        } catch (Exception $e) {
            $errors[] = '数据库错误: ' . $e->getMessage();
        }
    }

    // 2. 删除 config.lock
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }

    // 3. 显示结果
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>重置完成</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gradient-to-br from-purple-500 to-pink-500 min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
            <h1 class="text-2xl font-bold mb-4">重置完成</h1>
            <?php if (!empty($deletedTables)): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3 text-sm">
                    <p class="font-bold text-green-700 mb-1">✓ 已删除 <?= count($deletedTables) ?> 张表：</p>
                    <p class="text-green-600 text-xs"><?= htmlspecialchars(implode(', ', $deletedTables)) ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3 text-sm">
                    <?php foreach ($errors as $err): ?>
                        <p class="text-red-600">✗ <?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm text-blue-700">
                <p class="font-bold mb-1">下一步：</p>
                <ol class="list-decimal list-inside space-y-1 text-xs">
                    <li>config.lock 已删除</li>
                    <li>访问 <a href="install.php" class="underline">install.php</a> 重新安装</li>
                </ol>
            </div>
            <a href="install.php" class="block text-center py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                前往安装
            </a>
        </div>
    </body>
    </html>
    <?php
    @unlink(__FILE__);  // 自动删除自身
    exit;
}

// 显示确认页
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>一键重置 - MyGlassBlog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);min-height:100vh}</style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
        <h1 class="text-2xl font-bold text-red-600 mb-2">⚠️ 一键重置</h1>
        <p class="text-gray-600 text-sm mb-4">
            此操作将：
        </p>
        <ul class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-700 list-disc list-inside space-y-1">
            <li>删除数据库中<strong>所有 12 张表</strong>及数据</li>
            <li>删除 <code>config.lock</code> 文件</li>
            <li>删除自身 <code>reset.php</code></li>
        </ul>
        <p class="text-gray-600 text-sm mb-4">
            <?php if ($installed): ?>
                当前状态：<span class="text-red-600 font-bold">已安装</span>
            <?php else: ?>
                当前状态：<span class="text-gray-500">未安装</span>（点击将仅删除 lock 文件）
            <?php endif; ?>
        </p>

        <form method="POST">
            <button type="submit" name="confirm_reset" value="1"
                    onclick="return confirm('确认要清空所有数据吗？此操作不可逆！')"
                    class="w-full py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-bold mb-2">
                我已备份，确认重置
            </button>
            <a href="index.php" class="block text-center py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                取消
            </a>
        </form>
    </div>
</body>
</html>
<?php
