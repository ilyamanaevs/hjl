<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if ($username && $password) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, password FROM admins WHERE username = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Обновляем время последнего входа
            $update_query = "UPDATE admins SET last_login = NOW() WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(1, $admin['id'], PDO::PARAM_INT);
            $update_stmt->execute();
            
            redirect('index.php');
        } else {
            $error = 'Неверный логин или пароль';
        }
    } else {
        $error = 'Заполните все поля';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Вход в админ-панель</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Логин:</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Пароль:</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit">Войти</button>
            </form>
            
            <p><a href="../">← На сайт</a></p>
        </div>
    </div>
</body>
</html>