<?php
class Category {
    private $conn;
    private $table_name = "categories";
    
    public function __construct($db) {
        $this->conn = $db;
        // Устанавливаем кодировку для всех операций
        $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, slug, icon, description, sort_order) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(2, $data['slug'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['icon'], PDO::PARAM_STR);
        $stmt->bindParam(4, $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(5, $data['sort_order'], PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, slug = ?, icon = ?, description = ?, sort_order = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(2, $data['slug'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['icon'], PDO::PARAM_STR);
        $stmt->bindParam(4, $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(5, $data['sort_order'], PDO::PARAM_INT);
        $stmt->bindParam(6, $id, PDO::PARAM_INT);
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