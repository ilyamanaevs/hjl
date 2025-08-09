<?php
class UserContent {
    private $conn;
    private $table_name = "user_content";
    
    public function __construct($db) {
        $this->conn = $db;
        $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, category_id, title, text, type) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(3, $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(4, $data['text'], PDO::PARAM_STR);
        $stmt->bindParam(5, $data['type'], PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    public function getPending($limit = 20, $offset = 0) {
        $query = "SELECT uc.*, u.username, c.name as category_name 
                  FROM " . $this->table_name . " uc 
                  LEFT JOIN users u ON uc.user_id = u.id 
                  LEFT JOIN categories c ON uc.category_id = c.id 
                  WHERE uc.status = 'pending' 
                  ORDER BY uc.created_at DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function approve($id) {
        // Перемещаем в основную таблицу content
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            // Добавляем в основную таблицу
            $insert_query = "INSERT INTO content (category_id, title, text, type) VALUES (?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_query);
            $insert_stmt->bindParam(1, $item['category_id'], PDO::PARAM_INT);
            $insert_stmt->bindParam(2, $item['title'], PDO::PARAM_STR);
            $insert_stmt->bindParam(3, $item['text'], PDO::PARAM_STR);
            $insert_stmt->bindParam(4, $item['type'], PDO::PARAM_STR);
            
            if ($insert_stmt->execute()) {
                // Обновляем статус
                $update_query = "UPDATE " . $this->table_name . " SET status = 'approved' WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $id, PDO::PARAM_INT);
                return $update_stmt->execute();
            }
        }
        
        return false;
    }
    
    public function reject($id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'rejected' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getPendingCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>