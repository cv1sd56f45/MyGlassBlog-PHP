<?php
/**
 * MyGlassBlog PHP - 数据库配置
 * 
 * 宝塔面板部署时，请修改以下配置为你的实际数据库信息
 */

return [
    'db_host' => 'localhost',      // 数据库主机
    'db_port' => 3306,             // 端口
    'db_name' => 'myglassblog',    // 数据库名
    'db_user' => 'root',           // 用户名（宝塔默认为 bt_db_xxx 格式）
    'db_pass' => '',               // 密码（在宝塔数据库管理中查看）
    'db_charset' => 'utf8mb4',     // 字符集
    
    // 站点配置
    'site_url' => '',              // 站点URL（留空自动检测）
    'upload_dir' => 'uploads',     // 上传目录
    'debug' => false,              // 调试模式（生产环境请关闭）
    
    // 分页配置
    'posts_per_page' => 10,
    'chatters_per_page' => 20,
    'photos_per_page' => 30,
];
