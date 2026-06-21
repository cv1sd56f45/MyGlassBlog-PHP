<?php
/**
 * MyGlassBlog PHP - 文章模型
 */

class Post {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 获取文章列表
     */
    public function getList($page = 1, $perPage = 10, $status = 1) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM posts WHERE status = ? ORDER BY created_at DESC LIMIT ?, ?";
        return $this->db->query($sql, [$status, $offset, $perPage]);
    }
    
    /**
     * 获取文章总数
     */
    public function getCount($status = 1) {
        return $this->db->queryValue("SELECT COUNT(*) FROM posts WHERE status = ?", [$status]);
    }
    
    /**
     * 根据slug获取文章
     */
    public function getBySlug($slug) {
        return $this->db->queryOne("SELECT * FROM posts WHERE slug = ?", [$slug]);
    }
    
    /**
     * 根据ID获取文章
     */
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM posts WHERE id = ?", [$id]);
    }
    
    /**
     * 创建文章
     */
    public function create($data) {
        $sql = "INSERT INTO posts (title, slug, content, category, description, cover, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['category'] ?? '',
            $data['description'] ?? '',
            $data['cover'] ?? '',
            $data['status'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新文章
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['title', 'slug', 'content', 'category', 'description', 'cover', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }
    
    /**
     * 删除文章
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM posts WHERE id = ?", [$id]);
    }
    
    /**
     * 增加阅读量
     */
    public function incrementViews($id) {
        return $this->db->execute("UPDATE posts SET views = views + 1 WHERE id = ?", [$id]);
    }
    
    /**
     * 获取所有分类
     */
    public function getCategories() {
        return $this->db->query("SELECT DISTINCT category FROM posts WHERE category != '' ORDER BY category");
    }
}
