<?php
/**
 * MyGlassBlog PHP - 支付功能数据库更新脚本
 * 访问此文件会自动导入支付相关表结构
 * 执行完成后请删除此文件
 */
require_once __DIR__ . '/includes/functions.php';

// 检查是否是管理员
session_start();
if (!isset($_SESSION['admin_id'])) {
    die('请先<a href="admin/login.php">登录后台</a>');
}

$db = Database::getInstance()->getConnection();
$success = [];
$errors = [];

// 读取 SQL 文件
$sqlFile = __DIR__ . '/payment_schema.sql';
if (!file_exists($sqlFile)) {
    die('SQL 文件不存在：' . $sqlFile);
}

$sql = file_get_contents($sqlFile);

// 移除注释
$sql = preg_replace('/--.*$/m', '', $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

// 分割 SQL 语句
$statements = [];
$current = '';
$lines = explode("\n", $sql);

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    $current .= $line . ' ';
    if (preg_match('/;$/', $line)) {
        $statements[] = trim($current);
        $current = '';
    }
}

// 执行 SQL 语句
foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    
    try {
        // 检查表是否存在
        if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $stmt, $matches)) {
            $tableName = $matches[1];
            
            // 检查表是否存在
            $checkSql = "SHOW TABLES LIKE '{$tableName}'";
            $result = $db->query($checkSql);
            
            if ($result->rowCount() > 0) {
                $success[] = "表 `{$tableName}` 已存在，跳过创建";
                continue;
            }
        }
        
        $db->exec($stmt);
        $success[] = '执行成功：' . substr($stmt, 0, 100) . '...';
    } catch (PDOException $e) {
        // 忽略"表已存在"错误
        if ($e->getCode() != '42S01') {
            $errors[] = '执行失败：' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付功能数据库更新</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl w-full">
        <h1 class="text-2xl font-bold mb-6">支付功能数据库更新</h1>
        
        <?php if (!empty($success)): ?>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-green-600 mb-2">成功</h2>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <?php foreach ($success as $msg): ?>
                        <li class="text-green-700"><?= e($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-red-600 mb-2">错误</h2>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <?php foreach ($errors as $msg): ?>
                        <li class="text-red-700"><?= e($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="glass rounded-lg p-4 mb-4">
            <p class="font-bold mb-2">下一步操作：</p>
            <ol class="list-decimal list-inside space-y-1 text-sm">
                <li>进入后台 <a href="admin/payment.php" class="text-blue-500">支付配置</a> 页面</li>
                <li>添加你的易支付或码支付配置</li>
                <li>在赞赏页面测试在线支付功能</li>
                <li><strong class="text-red-500">删除此文件（update_payment_db.php）！</strong></li>
            </ol>
        </div>
        
        <div class="text-center">
            <a href="admin/payment.php" class="btn btn-primary">进入支付配置</a>
        </div>
    </div>
</body>
</html>
