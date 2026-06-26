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
     * 根据ID获取
     */
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM donations WHERE id = ?", [$id]);
    }
    
    /**
     * 创建
     */
    public function create($data) {
        $sql = "INSERT INTO donations (platform, qrcode, account_name, description, is_enabled, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['platform'],
            $data['qrcode'] ?? '',
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
        
        foreach (['platform', 'qrcode', 'account_name', 'description', 'is_enabled', 'sort_order'] as $field) {
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
        // 删除时一并获取二维码路径以便清理文件
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
}
