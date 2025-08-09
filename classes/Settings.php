<?php
class Settings {
    private $conn;
    private $table_name = "settings";
    
    public function __construct($db) {
        $this->conn = $db;
        // Устанавливаем кодировку для всех операций
        $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : null;
    }
    
    public function set($key, $value) {
        $query = "INSERT INTO " . $this->table_name . " (setting_key, setting_value) 
                  VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key, PDO::PARAM_STR);
        $stmt->bindParam(2, $value, PDO::PARAM_STR);
        $stmt->bindParam(3, $value, PDO::PARAM_STR);
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY setting_key";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>