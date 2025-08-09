<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Category.php';
require_once 'classes/Content.php';
require_once 'classes/Settings.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);
$content = new Content($db);
$settings = new Settings($db);

$slug = isset($_GET['slug']) ? sanitize_input($_GET['slug']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$cat_info = $category->getBySlug($slug);
if (!$cat_info) {
    redirect('index.php');
}

$items = $content->getByCategory($cat_info['id'], $limit, $offset);
$total_count = $content->getCount($cat_info['id']);
$total_pages = ceil($total_count / $limit);

updateStatistics('category_' . $slug);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($cat_info['name'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($settings->get('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
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
    <img src='style/img/<?php echo htmlspecialchars($cat_info['icon'], ENT_QUOTES, 'UTF-8'); ?>' alt='*'> 
    <?php echo htmlspecialchars($cat_info['name'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo $total_count; ?>)
</div>

<?php if (empty($items)): ?>
    <div class="news">
        <div class="inf">
            В данной категории пока нет материалов.
            <br><a href="index.php"><small>На главную</small></a>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($items as $item): ?>
        <div class="menue">
            <?php if ($item['title']): ?>
                <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
            <?php endif; ?>
            <?php echo nl2br(htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8')); ?>
            <br><small style="color: #999;">
                Просмотров: <?php echo $item['views']; ?> | 
                <?php echo formatDate($item['created_at']); ?>
            </small>
        </div>
    <?php endforeach; ?>
    
    <?php if ($total_pages > 1): ?>
        <div class="news">
            <div class="inf" style="text-align: center;">
                <?php if ($page > 1): ?>
                    <a href="?slug=<?php echo $slug; ?>&page=<?php echo $page-1; ?>">« Предыдущая</a>
                <?php endif; ?>
                
                Страница <?php echo $page; ?> из <?php echo $total_pages; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?slug=<?php echo $slug; ?>&page=<?php echo $page+1; ?>">Следующая »</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="frm">
    <form method="get" action="search.php"> 
        Поиск по сайту<br />
        <input class="radiusleft" name="q" type="text" value="" maxlength="50"/>
        <input type="submit" value="Поиск" class="radiusright" /><br />
        <input checked="checked" type="radio" name="by" value="sms" /> Статусы/смс 
        <input type="radio" name="by" value="fact" /> Факты
    </form>
</div>

<div class="foot"> 
    <a href='/'>
        <img src='style/img/on.png' alt='*'> <?php echo getOnlineUsers(); ?><small>чел</small>
    </a> 
</div>

</body>
</html>