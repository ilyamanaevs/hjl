<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Content.php';
require_once '../classes/Category.php';
require_once '../classes/Settings.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$content = new Content($db);
$category = new Category($db);
$settings = new Settings($db);

// Статистика
$total_content = $content->getCount();
$categories = $category->getAll();

// Получение статистики посещений
$stats_query = "SELECT page, SUM(visits) as total_visits FROM statistics GROUP BY page ORDER BY total_visits DESC LIMIT 10";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$page_stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <title>Админ-панель - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Админ-панель</h1>
        <div class="admin-nav">
            <a href="index.php" class="active">Главная</a>
            <a href="content.php">Контент</a>
            <a href="categories.php">Категории</a>
            <a href="settings.php">Настройки</a>
            <a href="logout.php">Выход</a>
        </div>
    </div>

    <div class="admin-content">
        <div class="dashboard">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Всего материалов</h3>
                    <div class="stat-number"><?php echo $total_content; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Категорий</h3>
                    <div class="stat-number"><?php echo count($categories); ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Онлайн</h3>
                    <div class="stat-number"><?php echo getOnlineUsers(); ?></div>
                </div>
            </div>

            <div class="recent-content">
                <h2>Статистика посещений</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Страница</th>
                            <th>Посещений</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($page_stats as $stat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['page'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $stat['total_visits']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="categories-overview">
                <h2>Категории</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Материалов</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $content->getCount($cat['id']); ?></td>
                            <td>
                                <a href="content.php?category=<?php echo $cat['id']; ?>">Просмотр</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>