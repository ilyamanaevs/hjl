<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Settings.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);

$message = '';

if ($_POST) {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            $settings->set($key, sanitize_input($value));
        }
    }
    $message = 'Настройки сохранены';
}

$all_settings = $settings->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <title>Настройки - Админ-панель</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Настройки сайта</h1>
        <div class="admin-nav">
            <a href="index.php">Главная</a>
            <a href="content.php">Контент</a>
            <a href="categories.php">Категории</a>
            <a href="settings.php" class="active">Настройки</a>
            <a href="logout.php">Выход</a>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="settings-form">
            <form method="post">
                <?php foreach ($all_settings as $setting): ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($setting['description'] ?: $setting['setting_key'], ENT_QUOTES, 'UTF-8'); ?>:</label>
                        <?php if (strlen($setting['setting_value']) > 100): ?>
                            <textarea name="<?php echo htmlspecialchars($setting['setting_key'], ENT_QUOTES, 'UTF-8'); ?>" rows="3"><?php echo htmlspecialchars($setting['setting_value'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php else: ?>
                            <input type="text" name="<?php echo htmlspecialchars($setting['setting_key'], ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($setting['setting_value'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <small>Ключ: <?php echo htmlspecialchars($setting['setting_key'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" name="submit">Сохранить настройки</button>
            </form>
        </div>

        <div class="admin-info">
            <h3>Информация о системе</h3>
            <p><strong>PHP версия:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Время сервера:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>Администратор:</strong> <?php echo $_SESSION['admin_username']; ?></p>
        </div>
    </div>
</body>
</html>