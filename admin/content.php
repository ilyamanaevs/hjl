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

$categories = $category->getAll();
$message = '';

// Обработка действий
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $data = [
            'category_id' => (int)$_POST['category_id'],
            'title' => sanitize_input($_POST['title']),
            'text' => sanitize_input($_POST['text']),
            'type' => sanitize_input($_POST['type'])
        ];
        
        if ($content->create($data)) {
            $message = 'Материал успешно добавлен';
        } else {
            $message = 'Ошибка при добавлении материала';
        }
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        if ($content->delete((int)$_POST['id'])) {
            $message = 'Материал удален';
        } else {
            $message = 'Ошибка при удалении';
        }
    }
}

// Получение списка контента
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

if ($category_filter) {
    $items = $content->getByCategory($category_filter, $limit, $offset);
    $total_count = $content->getCount($category_filter);
} else {
    // Получаем все материалы
    $query = "SELECT c.*, cat.name as category_name 
              FROM content c 
              LEFT JOIN categories cat ON c.category_id = cat.id 
              ORDER BY c.created_at DESC 
              LIMIT ? OFFSET ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $limit, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_count = $content->getCount();
}

$total_pages = ceil($total_count / $limit);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <title>Управление контентом - Админ-панель</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Управление контентом</h1>
        <div class="admin-nav">
            <a href="index.php">Главная</a>
            <a href="content.php" class="active">Контент</a>
            <a href="categories.php">Категории</a>
            <a href="settings.php">Настройки</a>
            <a href="logout.php">Выход</a>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="content-actions">
            <button onclick="toggleForm()" class="btn-primary">Добавить материал</button>
            
            <div class="filter-form">
                <form method="get">
                    <select name="category" onchange="this.form.submit()">
                        <option value="">Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div id="add-form" class="add-form" style="display: none;">
            <h3>Добавить новый материал</h3>
            <form method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Категория:</label>
                    <select name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Заголовок (необязательно):</label>
                    <input type="text" name="title">
                </div>
                
                <div class="form-group">
                    <label>Текст:</label>
                    <textarea name="text" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Тип:</label>
                    <select name="type" required>
                        <option value="status">Статус</option>
                        <option value="sms">SMS</option>
                        <option value="fact">Факт</option>
                        <option value="sound">Звук</option>
                    </select>
                </div>
                
                <button type="submit">Добавить</button>
                <button type="button" onclick="toggleForm()">Отмена</button>
            </form>
        </div>

        <div class="content-list">
            <h2>Материалы (<?php echo $total_count; ?>)</h2>
            
            <?php if (empty($items)): ?>
                <p>Материалы не найдены.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Категория</th>
                            <th>Заголовок</th>
                            <th>Текст</th>
                            <th>Тип</th>
                            <th>Просмотры</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo truncateText($item['text'], 50); ?></td>
                            <td><?php echo $item['type']; ?></td>
                            <td><?php echo $item['views']; ?></td>
                            <td><?php echo formatDate($item['created_at']); ?></td>
                            <td>
                                <form method="post" style="display: inline;" 
                                      onsubmit="return confirm('Удалить этот материал?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?><?php echo $category_filter ? '&category='.$category_filter : ''; ?>">« Предыдущая</a>
                        <?php endif; ?>
                        
                        <span>Страница <?php echo $page; ?> из <?php echo $total_pages; ?></span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?><?php echo $category_filter ? '&category='.$category_filter : ''; ?>">Следующая »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleForm() {
            var form = document.getElementById('add-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>