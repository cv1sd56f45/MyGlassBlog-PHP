<?php
/**
 * MyGlassBlog PHP - 后台登录
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// 已登录则跳转
if (isset($_SESSION['admin_id'])) {
    redirect(site_url('admin/index.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $db = Database::getInstance();
        $admin = $db->queryOne("SELECT * FROM admins WHERE username = ?", [$username]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect(site_url('admin/index.php'));
        } else {
            $error = '用户名或密码错误';
        }
    } else {
        $error = '请填写完整信息';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - MyGlassBlog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="glass rounded-2xl p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-white text-center mb-6">🔐 管理员登录</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-white px-4 py-3 rounded-lg mb-4">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-white/80 mb-2">用户名</label>
                <input type="text" name="username" required
                       class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:border-white/40"
                       placeholder="admin">
            </div>
            <div class="mb-6">
                <label class="block text-white/80 mb-2">密码</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:border-white/40"
                       placeholder="••••••••">
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-white/20 hover:bg-white/30 text-white font-medium transition-colors">
                登 录
            </button>
        </form>
        
        <p class="text-white/50 text-center mt-6 text-sm">
            默认账号: admin / admin123
        </p>
    </div>
</body>
</html>
