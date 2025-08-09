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

if (isset($_SESSION['user_id'])) {
    redirect('user_panel.php');
}

$error = '';

if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if ($username && $password) {
        $user_data = $user->login($username, $password);
        if ($user_data) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            redirect('user_panel.php');
        } else {
            $error = 'Неверный логин или пароль';
        }
    } else {
        $error = 'Заполните все поля';
    }
}

updateStatistics('login');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Вход - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
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
    <a href='register.php' class='ua'>Регистрация</a> 
    <a href="info.php?id=3" class='ua'>Контакты</a>
</div>

<div class="rz">
    <img src='style/img/rzi.png' alt='*'> Вход в систему
</div>

<div class="news">
    <div class="inf">
        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div style="margin-bottom: 10px;">
                <label>Имя пользователя или Email:</label><br>
                <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Пароль:</label><br>
                <input type="password" name="password" required>
            </div>
            
            <input type="submit" value="Войти">
        </form>
        
        <br><a href="register.php"><small>Нет аккаунта? Зарегистрироваться</small></a>
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