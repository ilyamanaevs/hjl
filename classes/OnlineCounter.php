<?php
class OnlineCounter {
    private $conn;
    private $table_name = "online_users";
    
    public function __construct($db) {
        $this->conn = $db;
        $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function updateUserActivity($user_ip, $user_agent = '') {
        $query = "INSERT INTO " . $this->table_name . " (ip_address, user_agent, last_activity) 
                  VALUES (?, ?, NOW()) 
                  ON DUPLICATE KEY UPDATE last_activity = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_ip, PDO::PARAM_STR);
        $stmt->bindParam(2, $user_agent, PDO::PARAM_STR);
        return $stmt->execute();
    }
    
    public function getRealOnlineCount() {
        // Удаляем старые записи (старше 5 минут)
        $cleanup_query = "DELETE FROM " . $this->table_name . " WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $this->conn->prepare($cleanup_query)->execute();
        
        // Считаем активных пользователей
        $query = "SELECT COUNT(DISTINCT ip_address) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }
    
    public function getTotalOnlineCount($settings) {
        $base_online = (int)$settings->get('online_users') ?: 333;
        $real_online = $this->getRealOnlineCount();
        return $base_online + $real_online;
    }
}
?>