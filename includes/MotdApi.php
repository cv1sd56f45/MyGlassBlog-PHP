<?php
/**
 * MyGlassBlog PHP - Minecraft 服务器状态查询 (MineBBS MOTD API)
 * API 文档: https://motd.minebbs.com/docs
 */

class MotdApi {
    private $apiBase = 'https://motd.minebbs.com/api/status';
    private $cacheDir = '';
    private $cacheTtl = 60; // 缓存60秒，避免频繁请求
    
    public function __construct() {
        $this->cacheDir = __DIR__ . '/../cache/motd/';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * 查询服务器状态
     * @param string $ip 服务器地址
     * @param int|null $port 端口（可选）
     * @param string $stype 查询类型: auto|java|bedrock
     * @param bool $srv 是否解析SRV记录
     * @return array
     */
    public function query($ip, $port = null, $stype = 'java', $srv = false) {
        // 生成缓存键
        $cacheKey = md5("{$ip}:{$port}:{$stype}:" . ($srv ? '1' : '0'));
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        // 检查缓存
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtl) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if ($cached && isset($cached['status'])) {
                $cached['_cached'] = true;
                $cached['_cache_time'] = date('Y-m-d H:i:s', filemtime($cacheFile));
                return $cached;
            }
        }
        
        // 构建请求参数
        $params = [
            'ip' => $ip,
            'stype' => $stype,
        ];
        if ($port) $params['port'] = $port;
        if ($srv) $params['srv'] = 'true';
        
        $url = $this->apiBase . '?' . http_build_query($params);
        
        // 发起HTTP请求
        $result = $this->httpGet($url);
        
        if ($result) {
            $data = json_decode($result, true);
            if ($data) {
                // 写入缓存
                file_put_contents($cacheFile, $result);
                $data['_cached'] = false;
                return $data;
            }
        }
        
        return [
            'status' => 'error',
            'host' => $ip . ($port ? ":{$port}" : ''),
            'error' => '无法连接到查询服务，请稍后重试。',
        ];
    }
    
    /**
     * 获取服务器状态图片URL
     */
    public function getImageUrl($ip, $port = null, $stype = 'java', $theme = 'simple') {
        $params = [
            'ip' => $ip,
            'stype' => $stype,
            'theme' => $theme,
        ];
        if ($port) $params['port'] = $port;
        return 'https://motd.minebbs.com/api/status_img?' . http_build_query($params);
    }
    
    /**
     * HTTP GET 请求
     */
    private function httpGet($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'MyGlassBlog-PHP/1.0',
            CURLOPT_HTTPHEADER => [
                'X-Internal-Request: true',
                'Accept: application/json',
            ],
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error || $httpCode !== 200) {
            return null;
        }
        
        return $response;
    }
    
    /**
     * 格式化玩家数
     */
    public static function formatPlayers($online, $max) {
        return "{$online} / {$max}";
    }
    
    /**
     * 获取状态样式类
     */
    public static function getStatusClass($status) {
        switch ($status) {
            case 'online': return 'text-green-400';
            case 'offline': return 'text-red-400';
            default: return 'text-yellow-400';
        }
    }
    
    /**
     * 获取状态文本
     */
    public static function getStatusText($status) {
        switch ($status) {
            case 'online': return '在线';
            case 'offline': return '离线';
            case 'error': return '查询失败';
            default: return '未知';
        }
    }
    
    /**
     * 获取服务器类型图标
     */
    public static function getTypeIcon($type) {
        switch (strtolower($type)) {
            case 'java': return '☕';
            case 'bedrock': return '🪨';
            default: return '🎮';
        }
    }
    
    /**
     * 清除缓存
     */
    public function clearCache() {
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '*.json');
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}
