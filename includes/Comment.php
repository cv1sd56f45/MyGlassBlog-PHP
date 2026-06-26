<?php
/**
 * MyGlassBlog PHP - 评论模型
 */

class Comment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getByPostId($postId, $status = 1) {
        return $this->db->query(
            "SELECT * FROM comments WHERE post_id = ? AND status = ? ORDER BY created_at ASC",
            [$postId, $status]
        );
    }
    
    public function getRecent($limit = 10) {
        return $this->db->query(
            "SELECT c.*, p.title as post_title FROM comments c 
             LEFT JOIN posts p ON c.post_id = p.id 
             WHERE c.status = 1 ORDER BY c.created_at DESC LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * 获取全部评论（分页）
     */
    public function getList($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        return $this->db->query(
            "SELECT c.*, p.title as post_title FROM comments c 
             LEFT JOIN posts p ON c.post_id = p.id 
             ORDER BY c.created_at DESC LIMIT ?, ?",
            [$offset, $perPage]
        );
    }
    
    /**
     * 获取已审核评论（分页）
     */
    public function getApproved($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        return $this->db->query(
            "SELECT c.*, p.title as post_title FROM comments c 
             LEFT JOIN posts p ON c.post_id = p.id 
             WHERE c.status = 1 ORDER BY c.created_at DESC LIMIT ?, ?",
            [$offset, $perPage]
        );
    }
    
    /**
     * 获取全部评论总数
     */
    public function getCount($status = null) {
        if ($status !== null) {
            $result = $this->db->queryOne("SELECT COUNT(*) as cnt FROM comments WHERE status = ?", [$status]);
        } else {
            $result = $this->db->queryOne("SELECT COUNT(*) as cnt FROM comments");
        }
        return $result ? $result['cnt'] : 0;
    }
    
    /**
     * 获取已审核评论总数
     */
    public function getApprovedCount() {
        return $this->getCount(1);
    }
    
    /**
     * 获取待审核评论（分页）
     */
    public function getPending($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        return $this->db->query(
            "SELECT c.*, p.title as post_title FROM comments c 
             LEFT JOIN posts p ON c.post_id = p.id 
             WHERE c.status = 0 ORDER BY c.created_at DESC LIMIT ?, ?",
            [$offset, $perPage]
        );
    }
    
    /**
     * 获取待审核评论总数
     */
    public function getPendingCount() {
        return $this->getCount(0);
    }
    
    public function create($data) {
        $sql = "INSERT INTO comments (post_id, parent_id, nickname, email, website, content, ip, user_agent, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['post_id'] ?? 0,
            $data['parent_id'] ?? 0,
            $data['nickname'],
            $data['email'] ?? '',
            $data['website'] ?? '',
            $data['content'],
            $data['ip'] ?? '',
            $data['user_agent'] ?? '',
            $data['status'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function delete($id) {
        return $this->db->execute("DELETE FROM comments WHERE id = ?", [$id]);
    }
    
    public function approve($id) {
        return $this->db->execute("UPDATE comments SET status = 1 WHERE id = ?", [$id]);
    }
    
    public function spam($id) {
        return $this->db->execute("UPDATE comments SET status = 2 WHERE id = ?", [$id]);
    }
}
