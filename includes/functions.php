<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function updateStatistics($page) {
    global $db;
    $today = date('Y-m-d');
    
    $query = "INSERT INTO statistics (page, visits, date) VALUES (?, 1, ?) 
              ON DUPLICATE KEY UPDATE visits = visits + 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $page, PDO::PARAM_STR);
    $stmt->bindParam(2, $today, PDO::PARAM_STR);
    $stmt->execute();
}

function getOnlineUsers() {
    global $settings;
    return $settings->get('online_users') ?: '347';
}
?>