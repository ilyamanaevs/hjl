<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Category.php';
require_once 'classes/Content.php';
require_once 'classes/Settings.php';
require_once 'classes/News.php';
require_once 'classes/OnlineCounter.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);
$content = new Content($db);
$settings = new Settings($db);
$news = new News($db);
$online = new OnlineCounter($db);

// Обновляем активность пользователя
$online->updateUserActivity($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

$categories = $category->getAll();
$site_title = $settings->get('site_title') ?: 'Классные статусы и СМС';
$latest_news = $news->getLatest();

updateStatistics('index');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8'); ?></title>
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
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href='user_panel.php' class='ua'>Кабинет</a>
    <?php else: ?>
        <a href='login.php' class='ua'>Вход</a>
    <?php endif; ?>
    <a href='info.php?id=1' class='ua'>Новости</a>
    <a href="info.php?id=3" class='ua'>Контакты</a>
</div>

<div class="news">
    <div class="inf">
        <?php if ($latest_news): ?>
            <strong><?php echo htmlspecialchars($latest_news['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
            <?php 
            $content_preview = truncateText($latest_news['content'], 200);
            echo nl2br(htmlspecialchars($content_preview, ENT_QUOTES, 'UTF-8')); 
            ?>
            <?php if (strlen($latest_news['content']) > 200): ?>
                ... <br>
            <?php else: ?>
                <br>
            <?php endif; ?>
        <?php else: ?>
            На нашем сайте Вы найдёте: онлайн Сборник 
            бесплатных смс с возможностью анонимной 
            отправки обычных и флеш смс через интернет.
            Классные статусы про любовь и жизнь, со
            смыслом, смешные статусы для вк и 
            одноклассников. А так же самые Интересные 
            факты о жизни, о человеке, о деньгах, про
            животных, факты обо всё.
            И это ещё не всё! ... <br>
        <?php endif; ?>
        <a href='/'><small>Далее</small></a>
    </div>
</div>

<div class="rz"><img src='style/img/rzi.png' alt='*'> Классные статусы, сборник смс</div>

<?php foreach ($categories as $cat): ?>
    <?php $count = $content->getCount($cat['id']); ?>
    <div class='menue'>
        <a href='category.php?slug=<?php echo urlencode($cat['slug']); ?>'>
            <img src='style/img/<?php echo htmlspecialchars($cat['icon'], ENT_QUOTES, 'UTF-8'); ?>' alt='*'> <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
        <span class="count"><?php echo $count; ?></span><br><br>
        <?php echo htmlspecialchars($cat['description'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endforeach; ?>

<div class="pnl">
    <table style="width:101%; margin:0;text-align: center">
        <tr>
            <?php if (isset($_SESSION['user_id'])): ?>
                <td><a href="user_panel.php"><img src="style/img/db.png" height="70" width="70"></a></td>
                <td><a href="user_panel.php"><img src="style/img/db1.png" height="70" width="70"></a></td>   
                <td><a href="user_panel.php"><img src="style/img/db2.png" height="70" width="70"></a></td>
            <?php else: ?>
                <td><a href="register.php"><img src="style/img/db.png" height="70" width="70"></a></td>
                <td><a href="register.php"><img src="style/img/db1.png" height="70" width="70"></a></td>   
                <td><a href="register.php"><img src="style/img/db2.png" height="70" width="70"></a></td>
            <?php endif; ?>
        </tr>
    </table>
</div>

<div class="frm">
    <form method="get" action="search.php"> 
        Поиск по сайту<br />
        <input class="radiusleft" name="q" type="text" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>" maxlength="50"/>
        <input type="submit" value="Поиск" class="radiusright" /><br />
        <input checked="checked" type="radio" name="by" value="sms" /> Статусы/смс 
        <input type="radio" name="by" value="fact" /> Факты
    </form>
</div>

<div class="opl"> Принимаем к оплате </div>

<div class="foot"> 
    <a href='/'>
        <img src='style/img/on.png' alt='*'> <?php echo $online->getTotalOnlineCount($settings); ?><small>чел</small>
    </a> 
</div>

</body>
</html>