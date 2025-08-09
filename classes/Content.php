<?php
class Content {
    private $conn;
    private $table_name = "content";
    
    public function __construct($db) {
        $this->conn = $db;
        // Устанавливаем кодировку для всех операций
        $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function getByCategory($category_id, $limit = 20, $offset = 0) {
        $query = "SELECT c.*, cat.name as category_name, cat.icon 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  WHERE c.category_id = ? AND c.is_active = 1 
                  ORDER BY c.created_at DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->bindParam(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function search($query, $type = 'all', $limit = 20) {
        $sql = "SELECT c.*, cat.name as category_name 
                FROM " . $this->table_name . " c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                WHERE c.is_active = 1 AND (c.text LIKE ? OR c.title LIKE ?)";
        
        if ($type !== 'all') {
            $sql .= " AND c.type = ?";
        }
        
        $sql .= " ORDER BY c.created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $search_term = "%{$query}%";
        $stmt->bindParam(1, $search_term, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_term, PDO::PARAM_STR);
        
        if ($type !== 'all') {
            $stmt->bindParam(3, $type, PDO::PARAM_STR);
            $stmt->bindParam(4, $limit, PDO::PARAM_INT);
        } else {
            $stmt->bindParam(3, $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  WHERE c.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function incrementViews($id) {
        $query = "UPDATE " . $this->table_name . " SET views = views + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getCount($category_id = null) {
        if ($category_id) {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE category_id = ? AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $category_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (category_id, title, text, type) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['text'], PDO::PARAM_STR);
        $stmt->bindParam(4, $data['type'], PDO::PARAM_STR);
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_id = ?, title = ?, text = ?, type = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['text'], PDO::PARAM_STR);
        $stmt->bindParam(4, $data['type'], PDO::PARAM_STR);
        $stmt->bindParam(5, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>