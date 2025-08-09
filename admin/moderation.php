<?php
session_start();
require_once '../config/database.php';
require_once '../classes/UserContent.php';
require_once '../classes/Settings.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$userContent = new UserContent($db);
$settings = new Settings($db);

$message = '';

if ($_POST) {
    $action = $_POST['action'];
    $id = (int)$_POST['id'];
    
    if ($action === 'approve') {
        if ($userContent->approve($id)) {
            $message = 'Материал одобрен и опубликован';
        } else {
            $message = 'Ошибка при одобрении';
        }
    }
    
    if ($action === 'reject') {
        if ($userContent->reject($id)) {
            $message = 'Материал отклонен';
        } else {
            $message = 'Ошибка при отклонении';
        }
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$pending_items = $userContent->getPending($limit, $offset);
$total_count = $userContent->getPendingCount();
$total_pages = ceil($total_count / $limit);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Модерация - Админ-панель</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Модерация материалов</h1>
        <div class="admin-nav">
            <a href="index.php">Главная</a>
            <a href="content.php">Контент</a>
            <a href="categories.php">Категории</a>
            <a href="moderation.php" class="active">Модерация</a>
            <a href="news.php">Новости</a>
            <a href="settings.php">Настройки</a>
            <a href="logout.php">Выход</a>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="moderation-list">
            <h2>Материалы на модерации (<?php echo $total_count; ?>)</h2>
            
            <?php if (empty($pending_items)): ?>
                <p>Нет материалов на модерации.</p>
            <?php else: ?>
                <?php foreach ($pending_items as $item): ?>
                    <div class="moderation-item">
                        <div class="item-header">
                            <strong>Автор:</strong> <?php echo htmlspecialchars($item['username'], ENT_QUOTES, 'UTF-8'); ?> | 
                            <strong>Категория:</strong> <?php echo htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8'); ?> | 
                            <strong>Тип:</strong> <?php echo $item['type']; ?> | 
                            <strong>Дата:</strong> <?php echo formatDate($item['created_at']); ?>
                        </div>
                        
                        <?php if ($item['title']): ?>
                            <div class="item-title">
                                <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <div class="item-content">
                            <?php echo nl2br(htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                        
                        <div class="item-actions">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-primary">Одобрить</button>
                            </form>
                            
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Отклонить этот материал?')">Отклонить</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>">« Предыдущая</a>
                        <?php endif; ?>
                        
                        <span>Страница <?php echo $page; ?> из <?php echo $total_pages; ?></span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?>">Следующая »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .moderation-item {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .item-header {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .item-title {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .item-content {
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .item-actions {
            text-align: right;
        }
    </style>
</body>
</html>