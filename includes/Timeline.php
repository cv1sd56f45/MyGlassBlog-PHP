<?php
/**
 * MyGlassBlog PHP - 时间线模型
 */

class Timeline {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getList($status = 1) {
        return $this->db->query("SELECT * FROM timeline WHERE status = ? ORDER BY event_date DESC, sort_order ASC", [$status]);
    }
    
    public function getById($id) {
        return $this->db->queryOne("SELECT * FROM timeline WHERE id = ?", [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO timeline (title, content, event_date, icon, sort_order, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->execute($sql, [
            $data['title'],
            $data['content'] ?? '',
            $data['event_date'],
            $data['icon'] ?? '',
            $data['sort_order'] ?? 0,
            $data['status'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['title', 'content', 'event_date', 'icon', 'sort_order', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE timeline SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        return $this->db->execute("DELETE FROM timeline WHERE id = ?", [$id]);
    }
}
