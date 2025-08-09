<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Settings.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$pages = [
    1 => ['title' => 'Новости', 'content' => 'Здесь будут размещаться новости сайта.'],
    2 => ['title' => 'Отзывы', 'content' => 'Отзывы пользователей о нашем сайте.'],
    3 => ['title' => 'Контакты', 'content' => 'Свяжитесь с нами: admin@statusms.ru']
];

$page_info = isset($pages[$id]) ? $pages[$id] : $pages[1];
updateStatistics('info_' . $id);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($page_info['title'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
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
    <a href='info.php?id=1' class='ua'>Новости</a> 
    <a href="info.php?id=3" class='ua'>Контакты</a>
</div>

<div class="rz">
    <img src='style/img/rzi.png' alt='*'> <?php echo htmlspecialchars($page_info['title'], ENT_QUOTES, 'UTF-8'); ?>
</div>

<div class="news">
    <div class="inf">
        <?php echo nl2br(htmlspecialchars($page_info['content'], ENT_QUOTES, 'UTF-8')); ?>
        <br><a href="index.php"><small>На главную</small></a>
    </div>
</div>

<div class="foot"> 
    <a href='/'>
        <img src='style/img/on.png' alt='*'> <?php echo getOnlineUsers(); ?><small>чел</small>
    </a> 
</div>

</body>
</html>