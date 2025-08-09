<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Устанавливаем кодировку для текущего соединения
$db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->exec("SET CHARACTER SET utf8mb4");
$db->exec("SET character_set_connection=utf8mb4");

// Создание таблиц
$queries = [
    // Таблица категорий
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci,
        slug VARCHAR(100) NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci,
        icon VARCHAR(50) COLLATE utf8mb4_unicode_ci,
        description TEXT,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Таблица контента (статусы, SMS, факты)
    "CREATE TABLE IF NOT EXISTS content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        title VARCHAR(255) COLLATE utf8mb4_unicode_ci,
        text TEXT NOT NULL COLLATE utf8mb4_unicode_ci,
        type ENUM('status', 'sms', 'fact', 'sound') NOT NULL COLLATE utf8mb4_unicode_ci,
        views INT DEFAULT 0,
        likes INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Таблица администраторов
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci,
        password VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci,
        email VARCHAR(100) COLLATE utf8mb4_unicode_ci,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Таблица настроек сайта
    "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci,
        setting_value TEXT COLLATE utf8mb4_unicode_ci,
        description VARCHAR(255) COLLATE utf8mb4_unicode_ci
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Таблица статистики
    "CREATE TABLE IF NOT EXISTS statistics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(100) COLLATE utf8mb4_unicode_ci,
        visits INT DEFAULT 0,
        date DATE,
        UNIQUE KEY unique_page_date (page, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

try {
    // Устанавливаем кодировку перед выполнением запросов
    $db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    foreach ($queries as $query) {
        $db->exec($query);
    }
    
    // Вставка начальных данных
    $categories_data = [
        ['Классные статусы', 'statuses', 'kl.png', 'Классные статусы - отборные, отсортированные по категориям статусы со всего интернета.', 1],
        ['Прикольные смс', 'funny-sms', 'pr.png', 'Прикольные смс - коллекция смешных, ярких и красочных смс!', 2],
        ['Смс любимым', 'love-sms', 'lu.png', 'Смс любимым - это красивые оригинальные смски для общения с дорогими вам людьми.', 3],
        ['Смс поздравления', 'congratulations', 'pz.png', 'Смс поздравления - куча поздравительных смс на любые торжества.', 4],
        ['Интересные факты', 'facts', 'fc.png', 'Интересные факты - подборка самых интересных и невероятных фактов о людях.', 5],
        ['Звуки на смс', 'sounds', 'zv.png', 'Звуки на смс - отличная сборка коротких звуков для смс оповещений.', 6]
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO categories (name, slug, icon, description, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($categories_data as $cat) {
        $stmt->execute($cat);
    }
    
    // Создание админа по умолчанию
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT IGNORE INTO admins (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $admin_password, 'admin@statusms.ru']);
    
    // Настройки сайта
    $settings_data = [
        ['site_title', 'Классные статусы и СМС', 'Название сайта'],
        ['online_users', '347', 'Количество онлайн пользователей'],
        ['meta_description', 'Классные статусы про любовь и жизнь, прикольные SMS', 'Описание сайта'],
        ['admin_email', 'admin@statusms.ru', 'Email администратора']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($settings_data as $setting) {
        $stmt->execute($setting);
    }
    
    echo "База данных успешно создана!<br>";
    echo "Логин админа: admin<br>";
    echo "Пароль админа: admin123<br>";
    echo "<a href='index.php'>Перейти на сайт</a> | <a href='admin/'>Админ-панель</a>";
    
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>