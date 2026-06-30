<?php
/**
 * MyGlassBlog PHP v1.5.0 - 一键安装向导
 *
 * 特性：
 *  1. 全自动建库建表（含 12 张表）
 *  2. "清空重建"模式：可选删除所有旧表
 *  3. 智能检测 config.php 可写性（目录或文件）
 *  4. 安装完成后自动删除自身
 */

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// 已安装锁定
if (file_exists(__DIR__ . '/config.lock') && $step !== 99) {
    // 提供一个"强制重装"入口
    if (isset($_GET['force']) && $_GET['force'] === '1') {
        // 允许进入第 99 步"重置"
        $step = 99;
    } else {
        die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>已安装</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh}</style>
</head><body class="flex items-center justify-center p-4">
<div class="bg-white/95 rounded-2xl shadow-xl p-8 w-full max-w-md text-center">
<h1 class="text-2xl font-bold text-gray-800 mb-2">系统已安装</h1>
<p class="text-gray-500 mb-4">检测到 config.lock 文件。</p>
<p class="text-gray-500 mb-6">如需重新安装，请先删除 <code class="bg-gray-100 px-2 py-1 rounded">config.lock</code> 文件，或点击下方按钮强制重装：</p>
<a href="?force=1" class="block py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 mb-2">强制重新安装（清空数据）</a>
<a href="index.php" class="block py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">访问首页</a>
</div></body></html>');
    }
}

// 检测 config.php 写入位置（智能）
function getConfigPath() {
    return __DIR__ . '/includes/config.php';
}

function checkConfigWritable() {
    $path = getConfigPath();
    if (file_exists($path)) {
        return is_writable($path) ? 'exists_writable' : 'exists_not_writable';
    }
    $dir = dirname($path);
    return is_writable($dir) ? 'dir_writable' : 'dir_not_writable';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ============ Step 2: 数据库配置 + 导入 ============
    if ($step == 2) {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $port = intval($_POST['db_port'] ?? 3306);
        $name = trim($_POST['db_name'] ?? 'myglassblog');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_pass'] ?? '';
        $admin_user = trim($_POST['admin_user'] ?? 'admin');
        $admin_pass = $_POST['admin_pass'] ?? '';
        $drop_existing = isset($_POST['drop_existing']);  // 是否清空重建

        if (empty($user)) {
            $error = '数据库用户名不能为空';
        } elseif (strlen($admin_pass) < 6) {
            $error = '管理员密码至少6位';
        } else {
            try {
                $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                // 建库
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$name`");

                // 可选：清空所有表（如果选了"清空重建"）
                if ($drop_existing) {
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($tables as $t) {
                        $pdo->exec("DROP TABLE IF EXISTS `$t`");
                    }
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                }

                // 读取并导入 database.sql
                $sql = file_get_contents(__DIR__ . '/database.sql');
                if ($sql === false) {
                    throw new Exception('找不到 database.sql 文件');
                }

                // 分割 SQL 语句（简单分号切分，忽略注释）
                $sql = preg_replace('/--.*$/m', '', $sql);
                $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

                $statements = [];
                $current = '';
                foreach (explode("\n", $sql) as $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    $current .= $line . ' ';
                    if (preg_match('/;$/', $line)) {
                        $statements[] = trim($current);
                        $current = '';
                    }
                }
                if (trim($current) !== '') $statements[] = trim($current);

                foreach ($statements as $stmt) {
                    if ($stmt === '' || $stmt === ';') continue;
                    $pdo->exec($stmt);
                }

                // 写 config.php
                $configContent = "<?php\n/**\n * MyGlassBlog PHP 配置文件\n * 由 install.php 自动生成于 " . date('Y-m-d H:i:s') . "\n */\n\nreturn [\n    'db_host' => " . var_export($host, true) . ",\n    'db_port' => " . var_export($port, true) . ",\n    'db_name' => " . var_export($name, true) . ",\n    'db_user' => " . var_export($user, true) . ",\n    'db_pass' => " . var_export($pass, true) . ",\n    'db_charset' => 'utf8mb4',\n    'site_url' => '',\n    'upload_dir' => 'uploads',\n    'debug' => false,\n    'posts_per_page' => 10,\n    'chatters_per_page' => 20,\n    'photos_per_page' => 30,\n];\n";

                $configPath = getConfigPath();
                $writeOk = @file_put_contents($configPath, $configContent);
                if ($writeOk === false) {
                    throw new Exception('无法写入 config.php，请检查 includes/ 目录权限（建议 755）');
                }
                @chmod($configPath, 0644);

                // 更新管理员密码
                $hashed = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ? WHERE id = 1");
                $stmt->execute([$admin_user, $hashed]);

                // 写锁定文件
                file_put_contents(__DIR__ . '/config.lock', date('Y-m-d H:i:s') . " | admin: $admin_user");

                // 创建 uploads 目录（如不存在）
                $uploadDirs = ['uploads', 'uploads/photos', 'uploads/avatars', 'uploads/donate'];
                foreach ($uploadDirs as $d) {
                    $full = __DIR__ . '/' . $d;
                    if (!is_dir($full)) @mkdir($full, 0755, true);
                }

                $step = 3;  // 完成页

            } catch (PDOException $e) {
                $error = '数据库错误: ' . $e->getMessage();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - MyGlassBlog PHP v1.5.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="glass rounded-2xl shadow-xl p-8 w-full max-w-xl">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-2">MyGlassBlog PHP</h1>
        <p class="text-gray-500 text-center mb-2 text-sm">v1.5.0 一键安装</p>

        <?php if ($step == 99): ?>
            <!-- 强制重装模式 -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-700 text-sm">⚠️ 你正在进入<strong>强制重装模式</strong>。所有现有数据将被清空。</p>
            </div>
            <a href="?step=1&force=1" class="block text-center py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                我已备份，继续重装
            </a>
            <a href="index.php" class="block text-center py-2 mt-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                取消，返回首页
            </a>

        <?php elseif ($step != 3): ?>
            <!-- 进度条 -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full <?= $step >= 1 ? 'bg-purple-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-bold">1</div>
                    <div class="w-16 h-1 <?= $step >= 2 ? 'bg-purple-500' : 'bg-gray-300' ?>"></div>
                    <div class="w-8 h-8 rounded-full <?= $step >= 2 ? 'bg-purple-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-bold">2</div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <!-- 步骤1：环境检测 -->
            <h2 class="font-bold text-lg mb-4">环境检测</h2>
            <ul class="space-y-2 mb-6">
                <?php
                $checks = [
                    'PHP >= 7.4 (当前: ' . PHP_VERSION . ')' => version_compare(PHP_VERSION, '7.4.0', '>='),
                    'PDO MySQL 扩展' => extension_loaded('pdo_mysql'),
                    'JSON 扩展' => function_exists('json_encode'),
                    'includes/config.php 可写' => in_array(checkConfigWritable(), ['exists_writable', 'dir_writable']),
                ];
                $allPass = true;
                foreach ($checks as $name => $pass):
                    if (!$pass) $allPass = false;
                ?>
                    <li class="flex items-center">
                        <span class="<?= $pass ? 'text-green-500' : 'text-red-500' ?> mr-2 font-bold"><?= $pass ? '✓' : '✗' ?></span>
                        <span class="<?= $pass ? '' : 'text-red-600' ?>"><?= htmlspecialchars($name) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($allPass): ?>
                <a href="?step=2<?= isset($_GET['force']) ? '&force=1' : '' ?>" class="block text-center py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                    下一步
                </a>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3 text-sm text-yellow-700">
                    <p class="font-bold mb-1">修复建议：</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>宝塔面板：网站根目录 → 权限 → 755 所有者 www</li>
                        <li>命令行：<code>chmod -R 755 includes/</code>（如需写入，可临时 777）</li>
                        <li>或在宝塔「文件」中手动创建 <code>includes/config.php</code> 并给 644 权限</li>
                    </ul>
                </div>
            <?php endif; ?>

        <?php elseif ($step == 2): ?>
            <!-- 步骤2：数据库配置 + 管理员设置（一步完成） -->
            <form method="POST" action="?step=2<?= isset($_GET['force']) ? '&force=1' : '' ?>">
                <h2 class="font-bold text-lg mb-4">数据库 + 管理员</h2>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-xs text-blue-700">
                    <p class="font-bold mb-1">💡 提示：</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>宝塔创建数据库后，填入对应信息</li>
                        <li>默认管理员：<code>admin</code> / <code>admin123</code>（建议立即修改）</li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-gray-700 mb-1 text-sm">数据库主机</label>
                            <input type="text" name="db_host" value="localhost" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 text-sm">端口</label>
                            <input type="number" name="db_port" value="3306" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">数据库名</label>
                        <input type="text" name="db_name" value="myglassblog" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">数据库用户名</label>
                        <input type="text" name="db_user" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">数据库密码</label>
                        <input type="password" name="db_pass"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <hr class="my-2">

                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">管理员用户名</label>
                        <input type="text" name="admin_user" value="admin" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">管理员密码（至少6位）</label>
                        <input type="password" name="admin_pass" required minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <label class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3 cursor-pointer">
                        <input type="checkbox" name="drop_existing" value="1" class="mt-1">
                        <span class="text-sm text-yellow-800">
                            <strong>清空重建</strong>：删除数据库中所有现有表（包括旧版本残留），然后重新创建 12 张新表。<br>
                            <span class="text-xs text-yellow-600">⚠️ 旧数据会全部丢失，请确认已备份</span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="w-full mt-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 font-bold">
                    开始安装
                </button>
            </form>

        <?php elseif ($step == 3): ?>
            <!-- 步骤3：完成 -->
            <div class="text-center py-4">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="font-bold text-xl mb-2">安装完成！</h2>
                <p class="text-gray-600 mb-6 text-sm">
                    12 张表已创建，管理员账号已设置。<br>
                    建议立即登录后台修改默认密码。
                </p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 text-left text-xs text-yellow-700">
                    <p class="font-bold mb-1">📝 后续步骤：</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>登录后台，修改默认密码</li>
                        <li>「站点设置」→ 填写标题、作者、ICP 备案号</li>
                        <li>「捐款/赞赏」→ 添加收款码</li>
                        <li>「支付配置」→ 配置易支付/码支付（可选）</li>
                        <li>删除服务器上的 <code>install.php</code>（已自动删除本文件）</li>
                    </ol>
                </div>

                <div class="space-y-3">
                    <a href="index.php" class="block py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                        访问首页
                    </a>
                    <a href="admin/login.php" class="block py-2 border border-purple-500 text-purple-500 rounded-lg hover:bg-purple-50">
                        进入后台
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// 安装成功后自动删除自身
if ($step == 3) {
    @unlink(__FILE__);
}
?>
