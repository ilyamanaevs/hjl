<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Category.php';
require_once '../classes/Content.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);
$content = new Content($db);

$message = '';

// Обработка действий
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $data = [
            'name' => sanitize_input($_POST['name']),
            'slug' => sanitize_input($_POST['slug']),
            'icon' => sanitize_input($_POST['icon']),
            'description' => sanitize_input($_POST['description']),
            'sort_order' => (int)$_POST['sort_order']
        ];
        
        if ($category->create($data)) {
            $message = 'Категория успешно добавлена';
        } else {
            $message = 'Ошибка при добавлении категории';
        }
    }
    
    if ($action === 'edit' && isset($_POST['id'])) {
        $data = [
            'name' => sanitize_input($_POST['name']),
            'slug' => sanitize_input($_POST['slug']),
            'icon' => sanitize_input($_POST['icon']),
            'description' => sanitize_input($_POST['description']),
            'sort_order' => (int)$_POST['sort_order']
        ];
        
        if ($category->update((int)$_POST['id'], $data)) {
            $message = 'Категория обновлена';
        } else {
            $message = 'Ошибка при обновлении';
        }
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $cat_id = (int)$_POST['id'];
        $count = $content->getCount($cat_id);
        
        if ($count > 0) {
            $message = 'Нельзя удалить категорию с материалами';
        } else {
            if ($category->delete($cat_id)) {
                $message = 'Категория удалена';
            } else {
                $message = 'Ошибка при удалении';
            }
        }
    }
}

$categories = $category->getAll();
$edit_category = null;

if (isset($_GET['edit'])) {
    $edit_category = $category->getById((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <title>Управление категориями - Админ-панель</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Управление категориями</h1>
        <div class="admin-nav">
            <a href="index.php">Главная</a>
            <a href="content.php">Контент</a>
            <a href="categories.php" class="active">Категории</a>
            <a href="settings.php">Настройки</a>
            <a href="logout.php">Выход</a>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="category-form">
            <h3><?php echo $edit_category ? 'Редактировать категорию' : 'Добавить категорию'; ?></h3>
            <form method="post">
                <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Название:</label>
                    <input type="text" name="name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Slug (URL):</label>
                    <input type="text" name="slug" value="<?php echo $edit_category ? htmlspecialchars($edit_category['slug'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Иконка (имя файла):</label>
                    <input type="text" name="icon" value="<?php echo $edit_category ? htmlspecialchars($edit_category['icon'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Описание:</label>
                    <textarea name="description" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['description'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Порядок сортировки:</label>
                    <input type="number" name="sort_order" value="<?php echo $edit_category ? $edit_category['sort_order'] : '0'; ?>">
                </div>
                
                <button type="submit"><?php echo $edit_category ? 'Обновить' : 'Добавить'; ?></button>
                <?php if ($edit_category): ?>
                    <a href="categories.php" class="btn-secondary">Отмена</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="categories-list">
            <h2>Список категорий</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Иконка</th>
                        <th>Материалов</th>
                        <th>Порядок</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($cat['icon'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $content->getCount($cat['id']); ?></td>
                        <td><?php echo $cat['sort_order']; ?></td>
                        <td>
                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn-primary">Редактировать</a>
                            <form method="post" style="display: inline;" 
                                  onsubmit="return confirm('Удалить эту категорию?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
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