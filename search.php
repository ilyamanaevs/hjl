<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Content.php';
require_once 'classes/Settings.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$content = new Content($db);
$settings = new Settings($db);

$query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
$type = isset($_GET['by']) ? sanitize_input($_GET['by']) : 'sms';

$results = [];
if ($query) {
    $search_type = ($type === 'fact') ? 'fact' : 'all';
    $results = $content->search($query, $search_type);
    updateStatistics('search');
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Поиск: <?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
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
    <img src='style/img/rzi.png' alt='*'> 
    Результаты поиска: "<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>"
</div>

<?php if (empty($query)): ?>
    <div class="news">
        <div class="inf">
            Введите поисковый запрос.
            <br><a href="index.php"><small>На главную</small></a>
        </div>
    </div>
<?php elseif (empty($results)): ?>
    <div class="news">
        <div class="inf">
            По вашему запросу ничего не найдено.
            <br><a href="index.php"><small>На главную</small></a>
        </div>
    </div>
<?php else: ?>
    <div class="news">
        <div class="inf">
            Найдено результатов: <?php echo count($results); ?>
        </div>
    </div>
    
    <?php foreach ($results as $item): ?>
        <div class="menue">
            <?php if ($item['title']): ?>
                <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
            <?php endif; ?>
            <?php echo nl2br(htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8')); ?>
            <br><small style="color: #999;">
                Категория: <?php echo htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8'); ?> | 
                Просмотров: <?php echo $item['views']; ?>
            </small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="frm">
    <form method="get" action="search.php"> 
        Поиск по сайту<br />
        <input class="radiusleft" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" maxlength="50"/>
        <input type="submit" value="Поиск" class="radiusright" /><br />
        <input <?php echo ($type === 'sms') ? 'checked="checked"' : ''; ?> type="radio" name="by" value="sms" /> Статусы/смс 
        <input <?php echo ($type === 'fact') ? 'checked="checked"' : ''; ?> type="radio" name="by" value="fact" /> Факты
    </form>
</div>

<div class="foot"> 
    <a href='/'>
        <img src='style/img/on.png' alt='*'> <?php echo getOnlineUsers(); ?><small>чел</small>
    </a> 
</div>

</body>
</html>