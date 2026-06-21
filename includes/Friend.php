<?php
/**
 * MyGlassBlog PHP - 友链模型
 */

class Friend {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getList($status = 1) {
        return $this->db->query("SELECT * FROM friends WHERE status = ? ORDER BY sort_order ASC, id ASC", [$status]);
    }
    
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM friends WHERE id = ?", [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO friends (name, url, avatar, description, sort_order, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['name'],
            $data['url'],
            $data['avatar'] ?? '',
            $data['description'] ?? '',
            $data['sort_order'] ?? 0,
            $data['status'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['name', 'url', 'avatar', 'description', 'sort_order', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE friends SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        return $this->db->execute("DELETE FROM friends WHERE id = ?", [$id]);
    }
}
