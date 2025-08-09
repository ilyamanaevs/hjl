<?php
class News {
    private $conn;
    private $table_name = "news";
    
    public function __construct($db) {
        $this->conn = $db;
        $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (title, content, is_active) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(2, $data['content'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['is_active'], PDO::PARAM_BOOL);
        return $stmt->execute();
    }
    
    public function getLatest() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET title = ?, content = ?, is_active = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(2, $data['content'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['is_active'], PDO::PARAM_BOOL);
        $stmt->bindParam(4, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>