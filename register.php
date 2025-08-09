<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Settings.php';
require_once 'classes/OnlineCounter.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$settings = new Settings($db);
$online = new OnlineCounter($db);

// Обновляем активность пользователя
$online->updateUserActivity($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

$error = '';
$success = '';

if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($user->register($username, $email, $password)) {
        $success = 'Регистрация успешна! Теперь вы можете войти в систему.';
    } else {
        $error = 'Пользователь с таким именем или email уже существует';
    }
}

updateStatistics('register');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Регистрация - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="shortcut icon" href="style/img/favicon.ico" />
    <link rel="stylesheet" href="style/style.css" type="text/css" />
</head>
<body>

<div class="logo">
    <table style="width:102%;margin:0px;text-align: center">
        <tr>
            <td class="tl"><a href="/"><img src="style/img/1.png" height="25" width="25"></a></td>
            <td><a href="/"><img src="style/img/logo.png" height="70" width="140"></a></td>
            <td><a href="/"><img src="style/img/2.png" height="25" width="25"></a></td>
        </tr>
    </table>
</div>

<div class="bzx4">
    <a href='index.php' class='ua'>Главная</a>
    <a href='login.php' class='ua'>Вход</a> 
    <a href="info.php?id=3" class='ua'>Контакты</a>
</div>

<div class="rz">
    <img src='style/img/rzi.png' alt='*'> Регистрация
</div>

<div class="news">
    <div class="inf">
        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="color: green; margin-bottom: 10px;"><?php echo $success; ?></div>
            <a href="login.php"><small>Войти в систему</small></a>
        <?php else: ?>
            <form method="post">
                <div style="margin-bottom: 10px;">
                    <label>Имя пользователя:</label><br>
                    <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <label>Email:</label><br>
                    <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <label>Пароль:</label><br>
                    <input type="password" name="password" required>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <label>Подтвердите пароль:</label><br>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <input type="submit" value="Зарегистрироваться">
            </form>
            
            <br><a href="login.php"><small>Уже есть аккаунт? Войти</small></a>
        <?php endif; ?>
        
        <br><a href="index.php"><small>На главную</small></a>
    </div>
</div>

<div class="foot"> 
    <a href='/'>
        <img src='style/img/on.png' alt='*'> <?php echo $online->getTotalOnlineCount($settings); ?><small>чел</small>
    </a> 
</div>

</body>
</html>