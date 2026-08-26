<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$search = number($_GET['search'] ?? 0);

if (isset($_POST['search_player'])) {
    $login = checking($_POST['login'] ?? '');
    if (!empty($login)) {
        $count_user = fch("SELECT * FROM `users` WHERE `login` = ? LIMIT 1", array($login));
        if ($count_user) {
            header("Location: /profile.php?id=" . $count_user['id']);
            exit();
        } else {
            err("Hero not found!");
        }
    }
}

head("Online Heroes", $uid);

$count = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 10800));

if ($search == 2) {
?>
    <div class="tophead cntr">
        Hero Name:<br>
        <form action="?search=2" method="post">
            <input type="text" name="login" maxlength="50" required placeholder="Enter hero name..." /><br><br>
            <input type="submit" name="search_player" value="Search Hero">
        </form>
    </div>
    <a href="/online.php" class="tophead cntr">← Back to Online List</a>
<?php
    foot($uid);
    exit();
}

$str = 10;
$url = "/online.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$user_q = $BaseConnect->prepare("SELECT * FROM `users` WHERE `time_on` > :t ORDER BY `level` DESC LIMIT :start, :limit");
$user_q->bindValue(':t', time() - 10800, PDO::PARAM_INT);
$user_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$user_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$user_q->execute();

$i = 1;
while ($users = $user_q->fetch(PDO::FETCH_ASSOC)) {
    $class = ($i % 2 == 0) ? 'list2' : 'list';
    echo '<div class="' . $class . '">';
    echo user_list($users['id']);
    echo '</div>';
    $i++;
}

ppage($url, $num, $str, $result);
?>
<a href="?search=2" class="tophead cntr">🔍 Search for Hero</a>
<?php
foot($uid);
?>