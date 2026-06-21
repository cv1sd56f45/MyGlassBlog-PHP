<?php
/**
 * MyGlassBlog PHP - 照片墙模型
 */

class Photo {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 获取照片列表
     */
    public function getList($page = 1, $perPage = 30, $status = 1) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM photos WHERE status = ? ORDER BY sort_order DESC, id DESC LIMIT ?, ?";
        return $this->db->query($sql, [$status, $offset, $perPage]);
    }
    
    /**
     * 获取所有照片（不分页）
     */
    public function getAll($status = 1) {
        return $this->db->query("SELECT * FROM photos WHERE status = ? ORDER BY sort_order DESC, id DESC", [$status]);
    }
    
    /**
     * 根据ID获取照片
     */
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM photos WHERE id = ?", [$id]);
    }
    
    /**
     * 创建照片
     */
    public function create($data) {
        $sql = "INSERT INTO photos (title, description, url, thumb, sort_order, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['title'] ?? '',
            $data['description'] ?? '',
            $data['url'],
            $data['thumb'] ?? $data['url'],
            $data['sort_order'] ?? 0,
            $data['status'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新照片
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['title', 'description', 'url', 'thumb', 'sort_order', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE photos SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }
    
    /**
     * 删除照片
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM photos WHERE id = ?", [$id]);
    }
    
    /**
     * 获取最新N张照片（首页预览用）
     */
    public function getLatest($limit = 6) {
        return $this->db->query("SELECT * FROM photos WHERE status = 1 ORDER BY sort_order DESC, id DESC LIMIT ?", [$limit]);
    }
}
