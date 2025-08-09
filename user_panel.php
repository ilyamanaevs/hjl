<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/UserContent.php';
require_once 'classes/Category.php';
require_once 'classes/Settings.php';
require_once 'classes/OnlineCounter.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$userContent = new UserContent($db);
$category = new Category($db);
$settings = new Settings($db);
$online = new OnlineCounter($db);

// Обновляем активность пользователя
$online->updateUserActivity($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

$categories = $category->getAll();
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'add_content') {
        if (!$user->canAddContent($_SESSION['user_id'])) {
            $error = 'Превышен лимит добавления материалов (максимум 5 в час)';
        } else {
            $data = [
                'user_id' => $_SESSION['user_id'],
                'category_id' => (int)$_POST['category_id'],
                'title' => sanitize_input($_POST['title']),
                'text' => sanitize_input($_POST['text']),
                'type' => sanitize_input($_POST['type'])
            ];
            
            if (empty($data['text'])) {
                $error = 'Текст не может быть пустым';
            } elseif ($userContent->create($data)) {
                $message = 'Материал отправлен на модерацию';
            } else {
                $error = 'Ошибка при добавлении материала';
            }
        }
    }
    
    if ($action === 'logout') {
        session_destroy();
        redirect('index.php');
    }
}

updateStatistics('user_panel');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Личный кабинет - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
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
    <a href='#' onclick="document.getElementById('logout-form').submit();" class='ua'>Выход</a> 
    <a href="info.php?id=3" class='ua'>Контакты</a>
</div>

<div class="rz">
    <img src='style/img/rzi.png' alt='*'> Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!
</div>

<?php if ($message): ?>
    <div class="news">
        <div class="inf" style="color: green;">
            <?php echo $message; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="news">
        <div class="inf" style="color: red;">
            <?php echo $error; ?>
        </div>
    </div>
<?php endif; ?>

<div class="pnl">
    <table style="width:101%; margin:0;text-align: center">
        <tr>
            <td><a href="#" onclick="showAddForm('status')"><img src="style/img/db.png" height="70" width="70"></a></td>
            <td><a href="#" onclick="showAddForm('sms')"><img src="style/img/db1.png" height="70" width="70"></a></td>   
            <td><a href="#" onclick="showAddForm('fact')"><img src="style/img/db2.png" height="70" width="70"></a></td>
        </tr>
        <tr>
            <td style="color: #EDEDED; font-size: 11px;">Добавить статус</td>
            <td style="color: #EDEDED; font-size: 11px;">Добавить SMS</td>
            <td style="color: #EDEDED; font-size: 11px;">Добавить факт</td>
        </tr>
    </table>
</div>

<div id="add-form" style="display: none;">
    <div class="news">
        <div class="inf">
            <form method="post">
                <input type="hidden" name="action" value="add_content">
                <input type="hidden" name="type" id="content-type" value="">
                
                <div style="margin-bottom: 10px;">
                    <label>Категория:</label><br>
                    <select name="category_id" required style="width: 100%; padding: 5px;">
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <label>Заголовок (необязательно):</label><br>
                    <input type="text" name="title" style="width: 100%;">
                </div>
                
                <div style="margin-bottom: 10px;">
                    <label>Текст:</label><br>
                    <textarea name="text" rows="5" required style="width: 100%;"></textarea>
                </div>
                
                <input type="submit" value="Отправить на модерацию">
                <input type="button" value="Отмена" onclick="hideAddForm()">
            </form>
        </div>
    </div>
</div>

<div class="news">
    <div class="inf">
        <strong>Правила добавления материалов:</strong><br>
        • Максимум 5 материалов в час<br>
        • Все материалы проходят модерацию<br>
        • Запрещен спам и неприличный контент<br>
        • Материалы должны соответствовать выбранной категории
        <br><a href="index.php"><small>На главную</small></a>
    </div>
</div>

<form id="logout-form" method="post" style="display: none;">
    <input type="hidden" name="action" value="logout">
</form>

<div class="foot"> 
    <a href='/'>
        <img src='style/img/on.png' alt='*'> <?php echo $online->getTotalOnlineCount($settings); ?><small>чел</small>
    </a> 
</div>

<script>
function showAddForm(type) {
    document.getElementById('content-type').value = type;
    document.getElementById('add-form').style.display = 'block';
    
    var typeNames = {
        'status': 'статус',
        'sms': 'SMS',
        'fact': 'факт'
    };
    
    var form = document.getElementById('add-form');
    var label = form.querySelector('label');
    if (label) {
        form.querySelector('.inf').innerHTML = form.querySelector('.inf').innerHTML.replace(
            'Добавить материал', 
            'Добавить ' + typeNames[type]
        );
    }
}

function hideAddForm() {
    document.getElementById('add-form').style.display = 'none';
}
</script>

</body>
</html>