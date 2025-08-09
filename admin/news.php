<?php
session_start();
require_once '../config/database.php';
require_once '../classes/News.php';
require_once '../classes/Settings.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$news = new News($db);
$settings = new Settings($db);

$message = '';

if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $data = [
            'title' => sanitize_input($_POST['title']),
            'content' => sanitize_input($_POST['content']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($news->create($data)) {
            $message = 'Новость добавлена';
        } else {
            $message = 'Ошибка при добавлении новости';
        }
    }
    
    if ($action === 'edit' && isset($_POST['id'])) {
        $data = [
            'title' => sanitize_input($_POST['title']),
            'content' => sanitize_input($_POST['content']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($news->update((int)$_POST['id'], $data)) {
            $message = 'Новость обновлена';
        } else {
            $message = 'Ошибка при обновлении';
        }
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        if ($news->delete((int)$_POST['id'])) {
            $message = 'Новость удалена';
        } else {
            $message = 'Ошибка при удалении';
        }
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$news_items = $news->getAll($limit, $offset);
$edit_news = null;

if (isset($_GET['edit'])) {
    $edit_news = $news->getById((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Управление новостями - Админ-панель</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Управление новостями</h1>
        <div class="admin-nav">
            <a href="index.php">Главная</a>
            <a href="content.php">Контент</a>
            <a href="categories.php">Категории</a>
            <a href="moderation.php">Модерация</a>
            <a href="news.php" class="active">Новости</a>
            <a href="settings.php">Настройки</a>
            <a href="logout.php">Выход</a>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="news-form">
            <h3><?php echo $edit_news ? 'Редактировать новость' : 'Добавить новость'; ?></h3>
            <form method="post">
                <input type="hidden" name="action" value="<?php echo $edit_news ? 'edit' : 'add'; ?>">
                <?php if ($edit_news): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_news['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Заголовок:</label>
                    <input type="text" name="title" value="<?php echo $edit_news ? htmlspecialchars($edit_news['title'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Содержание:</label>
                    <textarea name="content" rows="10" required><?php echo $edit_news ? htmlspecialchars($edit_news['content'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" <?php echo ($edit_news && $edit_news['is_active']) || !$edit_news ? 'checked' : ''; ?>>
                        Активна
                    </label>
                </div>
                
                <button type="submit"><?php echo $edit_news ? 'Обновить' : 'Добавить'; ?></button>
                <?php if ($edit_news): ?>
                    <a href="news.php" class="btn-secondary">Отмена</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="news-list">
            <h2>Список новостей</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Содержание</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news_items as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo truncateText($item['content'], 100); ?></td>
                        <td><?php echo $item['is_active'] ? 'Активна' : 'Неактивна'; ?></td>
                        <td><?php echo formatDate($item['created_at']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $item['id']; ?>" class="btn-primary">Редактировать</a>
                            <form method="post" style="display: inline;" 
                                  onsubmit="return confirm('Удалить эту новость?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>