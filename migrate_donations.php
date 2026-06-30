<?php
/**
 * 数据库迁移脚本 - 捐款表升级 v2
 * 访问此页面一键升级
 * 用后请删除此文件以确保安全
 */
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>数据库迁移 - 捐款表 v2</title>
    <style>
        body { font-family: 'Courier New', monospace; padding: 20px; background: #1a1a2e; color: #eee; }
        .ok { color: #4ade80; }
        .err { color: #f87171; font-weight: bold; }
        .info { color: #60a5fa; }
        .warn { color: #facc15; }
        pre { background: #000; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .box { background: #16213e; padding: 20px; border-radius: 10px; margin: 10px 0; }
        h1 { color: #f093fb; }
    </style>
</head>
<body>
<h1>🔧 数据库迁移 - 捐款表升级 v2</h1>
<div class="box">
<?php

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    // 1. 检查列是否存在
    $hasPayType = false;
    $hasLink = false;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM donations LIKE 'pay_type'");
    $hasPayType = $stmt->fetch() !== false;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM donations LIKE 'link'");
    $hasLink = $stmt->fetch() !== false;
    
    echo "<p>检查现有结构：</p>";
    echo "<pre>";
    echo "donations.pay_type: " . ($hasPayType ? '<span class="ok">✓ 存在</span>' : '<span class="warn">✗ 不存在</span>') . "\n";
    echo "donations.link: " . ($hasLink ? '<span class="ok">✓ 存在</span>' : '<span class="warn">✗ 不存在</span>') . "\n";
    echo "</pre>";
    
    // 2. 备份
    echo "<p class='info'>[1/4] 备份当前表结构...</p>";
    $stmt = $pdo->query("SHOW CREATE TABLE donations");
    $backup = $stmt->fetch(PDO::FETCH_ASSOC);
    $backupFile = __DIR__ . '/cache/donations_backup_' . date('Ymd_His') . '.sql';
    @mkdir(dirname($backupFile), 0755, true);
    file_put_contents($backupFile, $backup['Create Table'] . ";\n");
    echo "<p class='ok'>  ✓ 备份保存到: " . htmlspecialchars(basename($backupFile)) . "</p>";
    
    // 3. 添加 pay_type 列
    if (!$hasPayType) {
        echo "<p class='info'>[2/4] 添加 pay_type 列...</p>";
        $pdo->exec("ALTER TABLE `donations` ADD COLUMN `pay_type` VARCHAR(20) NOT NULL DEFAULT 'qrcode' COMMENT '支付类型' AFTER `platform`");
        echo "<p class='ok'>  ✓ pay_type 列添加成功</p>";
    } else {
        echo "<p class='info'>[2/4] pay_type 列已存在，跳过</p>";
    }
    
    // 4. 添加 link 列
    if (!$hasLink) {
        echo "<p class='info'>[3/4] 添加 link 列...</p>";
        $pdo->exec("ALTER TABLE `donations` ADD COLUMN `link` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义链接' AFTER `qrcode`");
        echo "<p class='ok'>  ✓ link 列添加成功</p>";
    } else {
        echo "<p class='info'>[3/4] link 列已存在，跳过</p>";
    }
    
    // 5. 更新现有数据
    echo "<p class='info'>[4/4] 更新现有数据...</p>";
    $pdo->exec("UPDATE `donations` SET `pay_type` = 'qrcode' WHERE `pay_type` = '' OR `pay_type` IS NULL");
    $pdo->exec("UPDATE `donations` SET `link` = '' WHERE `link` IS NULL");
    echo "<p class='ok'>  ✓ 现有数据已设置默认 pay_type=qrcode</p>";
    
    // 6. 添加设置项
    $settings = $pdo->query("SELECT COUNT(*) as cnt FROM settings WHERE site_key = 'icp_number'")->fetch();
    if ($settings['cnt'] == 0) {
        $pdo->exec("INSERT INTO `settings` (`site_key`, `site_value`, `description`) VALUES ('icp_number', '', 'ICP备案号')");
        echo "<p class='ok'>  ✓ 添加 icp_number 设置项</p>";
    } else {
        echo "<p class='info'>  · icp_number 设置项已存在</p>";
    }
    
    $settings = $pdo->query("SELECT COUNT(*) as cnt FROM settings WHERE site_key = 'donation_type'")->fetch();
    if ($settings['cnt'] == 0) {
        $pdo->exec("INSERT INTO `settings` (`site_key`, `site_value`, `description`) VALUES ('donation_type', 'qrcode', '默认捐赠类型')");
        echo "<p class='ok'>  ✓ 添加 donation_type 设置项</p>";
    } else {
        echo "<p class='info'>  · donation_type 设置项已存在</p>";
    }
    
    // 7. 显示最终结构
    echo "<p class='info'>最终 donations 表结构：</p>";
    echo "<pre>";
    $stmt = $pdo->query("DESCRIBE donations");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . "\t" . $row['Type'] . "\t" . $row['Null'] . "\t" . $row['Key'] . "\t" . $row['Default'] . "\n";
    }
    echo "</pre>";
    
    echo "<div class='box'>";
    echo "<h2 class='ok'>✅ 迁移完成！</h2>";
    echo "<p>现在可以访问 <a href='donate.php' style='color:#60a5fa'>donate.php</a> 测试。</p>";
    echo "<p class='warn'>⚠️ 安全提示：建议立即删除此文件 (migrate_donations.php)</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='err'>❌ 错误: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
</div>
</body>
</html>
