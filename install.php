<?php
/**
 * MyGlassBlog PHP - 安装向导
 * 
 * 访问 install.php 进行安装
 * 安装完成后会自动删除此文件
 */

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// 检查是否已安装
if (file_exists(__DIR__ . '/config.lock')) {
    die('系统已安装。如需重新安装，请删除 config.lock 文件。');
}

// 处理安装
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        // 测试数据库连接
        $host = $_POST['db_host'];
        $port = intval($_POST['db_port']);
        $name = $_POST['db_name'];
        $user = $_POST['db_user'];
        $pass = $_POST['db_pass'];
        
        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // 创建数据库
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            
            // 保存配置
            $config = "<?php
return [
    'db_host' => '$host',
    'db_port' => $port,
    'db_name' => '$name',
    'db_user' => '$user',
    'db_pass' => '$pass',
    'db_charset' => 'utf8mb4',
    'site_url' => '',
    'upload_dir' => 'uploads',
    'debug' => false,
    'posts_per_page' => 10,
    'chatters_per_page' => 20,
];
";
            
            file_put_contents(__DIR__ . '/includes/config.php', $config);
            
            // 导入数据库
            $sql = file_get_contents(__DIR__ . '/database.sql');
            $pdo->exec($sql);
            
            $step = 3;
        } catch (PDOException $e) {
            $error = '数据库连接失败: ' . $e->getMessage();
        }
    } else if ($step == 3) {
        // 完成安装
        $admin_user = trim($_POST['admin_user']);
        $admin_pass = $_POST['admin_pass'];
        
        if (strlen($admin_pass) < 6) {
            $error = '密码至少6位';
        } else {
            require_once __DIR__ . '/includes/functions.php';
            $db = Database::getInstance();
            
            $hashed = password_hash($admin_pass, PASSWORD_DEFAULT);
            $db->execute("UPDATE admins SET username = ?, password = ? WHERE id = 1", [$admin_user, $hashed]);
            
            // 创建锁文件
            file_put_contents(__DIR__ . '/config.lock', date('Y-m-d H:i:s'));
            
            // 删除安装文件（安全考虑）
            @unlink(__FILE__);
            
            $step = 4;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - MyGlassBlog PHP</title>
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
        <p class="text-gray-500 text-center mb-6">安装向导</p>
        
        <!-- 进度条 -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full <?= $step >= 1 ? 'bg-purple-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-bold">1</div>
                <div class="w-16 h-1 <?= $step >= 2 ? 'bg-purple-500' : 'bg-gray-300' ?>"></div>
                <div class="w-8 h-8 rounded-full <?= $step >= 2 ? 'bg-purple-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-bold">2</div>
                <div class="w-16 h-1 <?= $step >= 3 ? 'bg-purple-500' : 'bg-gray-300' ?>"></div>
                <div class="w-8 h-8 rounded-full <?= $step >= 4 ? 'bg-purple-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-bold">3</div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <!-- 步骤1：环境检测 -->
            <div class="text-gray-700">
                <h2 class="font-bold text-lg mb-4">环境检测</h2>
                
                <ul class="space-y-2 mb-6">
                    <?php
                    $checks = [
                        'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
                        'PDO 扩展' => extension_loaded('pdo_mysql'),
                        'JSON 扩展' => function_exists('json_encode'),
                        'config.php 可写' => (file_exists(__DIR__.'/includes/config.php') ? is_writable(__DIR__.'/includes/config.php') : is_writable(__DIR__.'/includes')),
                    ];
                    $allPass = true;
                    ?>
                    <?php foreach ($checks as $name => $pass): ?>
                        <li class="flex items-center">
                            <?php if ($pass): ?>
                                <span class="text-green-500 mr-2">✓</span>
                            <?php else: ?>
                                <span class="text-red-500 mr-2">✗</span>
                                <?php $allPass = false; ?>
                            <?php endif; ?>
                            <?= $name ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if ($allPass): ?>
                    <a href="?step=2" class="block text-center py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                        下一步
                    </a>
                <?php else: ?>
                    <p class="text-red-500 text-center">请先解决上述问题再继续安装</p>
                <?php endif; ?>
            </div>
            
        <?php elseif ($step == 2): ?>
            <!-- 步骤2：数据库配置 -->
            <form method="POST" action="?step=2">
                <h2 class="font-bold text-lg mb-4">数据库配置</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1">数据库主机</label>
                        <input type="text" name="db_host" value="localhost" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">端口</label>
                        <input type="number" name="db_port" value="3306" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">数据库名</label>
                        <input type="text" name="db_name" value="myglassblog" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">用户名</label>
                        <input type="text" name="db_user" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">密码</label>
                        <input type="password" name="db_pass"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                
                <button type="submit" class="w-full mt-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                    测试连接并安装
                </button>
            </form>
            
        <?php elseif ($step == 3): ?>
            <!-- 步骤3：管理员设置 -->
            <form method="POST" action="?step=3">
                <h2 class="font-bold text-lg mb-4">设置管理员账号</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1">用户名</label>
                        <input type="text" name="admin_user" value="admin" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">密码</label>
                        <input type="password" name="admin_pass" required minlength="6"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="text-sm text-gray-500 mt-1">至少6位</p>
                    </div>
                </div>
                
                <button type="submit" class="w-full mt-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                    完成安装
                </button>
            </form>
            
        <?php elseif ($step == 4): ?>
            <!-- 步骤4：完成 -->
            <div class="text-center">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="font-bold text-xl mb-2">安装完成！</h2>
                <p class="text-gray-600 mb-6">您的博客已成功安装。</p>
                
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
