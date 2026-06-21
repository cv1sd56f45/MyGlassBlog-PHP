<?php
/**
 * MyGlassBlog PHP - 核心函数库
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/Post.php';
require_once __DIR__ . '/Chatter.php';
require_once __DIR__ . '/Photo.php';
require_once __DIR__ . '/Friend.php';
require_once __DIR__ . '/Timeline.php';
require_once __DIR__ . '/Comment.php';

/**
 * 自动加载类
 */
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * 获取站点URL
 */
function site_url($path = '') {
    $config = require __DIR__ . '/../config.php';
    $baseUrl = $config['site_url'];
    
    if (empty($baseUrl)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
    
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

/**
 * 获取站点配置
 */
function site_config() {
    static $settings = null;
    if ($settings === null) {
        $settings = new Settings();
    }
    return $settings;
}

/**
 * 格式化时间
 */
function format_time($datetime, $format = 'Y-m-d H:i') {
    return date($format, strtotime($datetime));
}

/**
 * 相对时间
 */
function relative_time($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return '刚刚';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' 分钟前';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' 小时前';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' 天前';
    } else {
        return date('Y-m-d', $timestamp);
    }
}

/**
 * 截断文本
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * 生成slug
 */
function generate_slug($title) {
    // 简单的拼音转换或直接用时间戳
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9\s-]/', '', $title));
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    if (empty($slug)) {
        $slug = 'post-' . time();
    }
    
    return $slug;
}

/**
 * 安全输出HTML
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Markdown转HTML（简化版）
 */
function markdown_to_html($markdown) {
    // 简单的Markdown解析，生产环境建议使用 Parsedown 库
    $html = $markdown;
    
    // 代码块
    $html = preg_replace('/```(\w*)\n(.*?)```/s', '<pre><code class="language-$1">$2</code></pre>', $html);
    
    // 行内代码
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
    
    // 标题
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    
    // 粗体和斜体
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    
    // 链接
    $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank">$1</a>', $html);
    
    // 图片
    $html = preg_replace('/!\[(.+?)\]\((.+?)\)/', '<img src="$2" alt="$1" loading="lazy">', $html);
    
    // 列表
    $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $html);
    
    // 引用
    $html = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $html);
    
    // 段落
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    $html = str_replace('<p></p>', '', $html);
    $html = str_replace('<p><pre>', '<pre>', $html);
    $html = str_replace('</pre></p>', '</pre>', $html);
    $html = str_replace('<p><ul>', '<ul>', $html);
    $html = str_replace('</ul></p>', '</ul>', $html);
    
    return $html;
}

/**
 * 分页生成
 */
function pagination($current, $total, $perPage, $url) {
    $pages = ceil($total / $perPage);
    if ($pages <= 1) return '';
    
    $html = '<div class="pagination">';
    
    if ($current > 1) {
        $html .= '<a href="' . $url . '?page=' . ($current - 1) . '" class="prev">上一页</a>';
    }
    
    for ($i = max(1, $current - 2); $i <= min($pages, $current + 2); $i++) {
        if ($i == $current) {
            $html .= '<span class="current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url . '?page=' . $i . '">' . $i . '</a>';
        }
    }
    
    if ($current < $pages) {
        $html .= '<a href="' . $url . '?page=' . ($current + 1) . '" class="next">下一页</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * 客户端IP
 */
function get_client_ip() {
    $ip = '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

/**
 * 重定向
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * JSON响应
 */
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
