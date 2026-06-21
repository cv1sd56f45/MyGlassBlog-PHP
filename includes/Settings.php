<?php
/**
 * MyGlassBlog PHP - 站点配置类
 */

class Settings {
    private $db;
    private $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadAll();
    }
    
    /**
     * 加载所有配置到缓存
     */
    private function loadAll() {
        $rows = $this->db->query("SELECT site_key, site_value FROM settings");
        foreach ($rows as $row) {
            $this->cache[$row['site_key']] = $row['site_value'];
        }
    }
    
    /**
     * 获取配置值
     */
    public function get($key, $default = '') {
        return $this->cache[$key] ?? $default;
    }
    
    /**
     * 设置配置值
     */
    public function set($key, $value) {
        $sql = "INSERT INTO settings (site_key, site_value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE site_value = VALUES(site_value)";
        $result = $this->db->execute($sql, [$key, $value]);
        if ($result) {
            $this->cache[$key] = $value;
        }
        return $result;
    }
    
    /**
     * 获取所有配置
     */
    public function all() {
        return $this->cache;
    }
    
    /**
     * 获取主题颜色数组
     */
    public function getThemeColors() {
        $colors = $this->get('theme_colors', '#a18cd1,#fbc2eb');
        return explode(',', $colors);
    }
    
    /**
     * 获取社交链接
     */
    public function getSocialLinks() {
        return [
            'github' => $this->get('social_github'),
            'twitter' => $this->get('social_twitter'),
            'email' => $this->get('social_email'),
            'wechat' => $this->get('social_wechat'),
        ];
    }
}
