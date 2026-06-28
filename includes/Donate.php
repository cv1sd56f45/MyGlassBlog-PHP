<?php
/**
 * MyGlassBlog PHP - 捐款/赞赏模型
 */

class Donate {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 获取所有启用的捐款平台列表
     */
    public function getList($enabledOnly = true) {
        $sql = "SELECT * FROM donations";
        if ($enabledOnly) {
            $sql .= " WHERE is_enabled = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        return $this->db->query($sql);
    }
    
    /**
     * 根据支付类型获取列表
     */
    public function getByPayType($payType) {
        $sql = "SELECT * FROM donations WHERE is_enabled = 1 AND pay_type = ? ORDER BY sort_order ASC, id ASC";
        return $this->db->query($sql, [$payType]);
    }
    
    /**
     * 根据ID获取
     */
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM donations WHERE id = ?", [$id]);
    }
    
    /**
     * 创建
     */
    public function create($data) {
        $sql = "INSERT INTO donations (platform, pay_type, qrcode, link, account_name, description, is_enabled, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['platform'],
            $data['pay_type'] ?? 'qrcode',
            $data['qrcode'] ?? '',
            $data['link'] ?? '',
            $data['account_name'] ?? '',
            $data['description'] ?? '',
            $data['is_enabled'] ?? 1,
            $data['sort_order'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['platform', 'pay_type', 'qrcode', 'link', 'account_name', 'description', 'is_enabled', 'sort_order'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE donations SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }
    
    /**
     * 删除
     */
    public function delete($id) {
        $item = $this->getById($id);
        if ($item) {
            $this->db->execute("DELETE FROM donations WHERE id = ?", [$id]);
            return $item;
        }
        return false;
    }
    
    /**
     * 获取总数
     */
    public function getCount() {
        $result = $this->db->queryOne("SELECT COUNT(*) as cnt FROM donations");
        return $result ? $result['cnt'] : 0;
    }
    
    /**
     * 获取支付类型显示名称
     */
    public static function getPayTypeName($type) {
        $names = [
            'qrcode' => '收款二维码',
            'yipay' => '易支付',
            'mapay' => '码支付',
            'link' => '自定义链接'
        ];
        return $names[$type] ?? $type;
    }
}
