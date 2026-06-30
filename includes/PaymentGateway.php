<?php
/**
 * MyGlassBlog PHP - 支付网关配置模型
 */
class PaymentGateway {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }
    
    /**
     * 创建配置
     */
    public function create($data) {
        $sql = "INSERT INTO `payment_gateways` (`gateway_type`, `gateway_name`, `pid`, `key`, `api_url`, `is_enabled`) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['gateway_type'],
            $data['gateway_name'],
            $data['pid'],
            $data['key'],
            $data['api_url'],
            $data['is_enabled'] ?? 0,
        ]);
    }
    
    /**
     * 更新配置
     */
    public function update($id, $data) {
        $sql = "UPDATE `payment_gateways` SET 
                `gateway_type` = ?, 
                `gateway_name` = ?, 
                `pid` = ?, 
                `key` = ?, 
                `api_url` = ?, 
                `is_enabled` = ? 
                WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['gateway_type'],
            $data['gateway_name'],
            $data['pid'],
            $data['key'],
            $data['api_url'],
            $data['is_enabled'] ?? 0,
            $id,
        ]);
    }
    
    /**
     * 删除配置
     */
    public function delete($id) {
        $sql = "DELETE FROM `payment_gateways` WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取所有配置
     */
    public function getList() {
        $sql = "SELECT * FROM `payment_gateways` ORDER BY `id` ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据ID获取
     */
    public function getById($id) {
        $sql = "SELECT * FROM `payment_gateways` WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据网关类型获取
     */
    public function getByType($gateway_type) {
        $sql = "SELECT * FROM `payment_gateways` WHERE `gateway_type` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$gateway_type]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取已启用的网关
     */
    public function getEnabled() {
        $sql = "SELECT * FROM `payment_gateways` WHERE `is_enabled` = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取站点URL（用于配置回调地址）
     */
    public static function getSiteUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'];
    }
}
