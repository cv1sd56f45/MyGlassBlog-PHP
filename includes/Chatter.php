<?php
/**
 * MyGlassBlog PHP - 说说模型
 */

class Chatter {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 获取说说列表
     */
    public function getList($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM chatters ORDER BY created_at DESC LIMIT ?, ?";
        return $this->db->query($sql, [$offset, $perPage]);
    }
    
    /**
     * 获取说说总数
     */
    public function getCount() {
        return $this->db->queryValue("SELECT COUNT(*) FROM chatters");
    }
    
    /**
     * 根据ID获取说说
     */
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM chatters WHERE id = ?", [$id]);
    }
    
    /**
     * 创建说说
     */
    public function create($content, $images = []) {
        $imagesJson = empty($images) ? '' : json_encode($images, JSON_UNESCAPED_UNICODE);
        $sql = "INSERT INTO chatters (content, images) VALUES (?, ?)";
        $this->db->execute($sql, [$content, $imagesJson]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 删除说说
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM chatters WHERE id = ?", [$id]);
    }
    
    /**
     * 获取最新N条说说（首页用）
     */
    public function getLatest($limit = 5) {
        return $this->db->query("SELECT * FROM chatters ORDER BY created_at DESC LIMIT ?", [$limit]);
    }
}
