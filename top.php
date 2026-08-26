<?php
$location = true;
require_once __DIR__ . "/core/func.php";

head("Hall of Fame - Top Warriors", $uid);
?>

<div class="list2">
    <small>
        <span style="float: left;">Hero</span>
        <span style="float: right;">Total Power</span>
        <br>
    </small>
</div>

<?php
$count = cnt("SELECT (id) FROM `users`");
$str  = 10;
$url = "/top.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$user_q = $BaseConnect->prepare("SELECT * FROM `users` ORDER BY `power`+`block`+`health` DESC, `id` DESC LIMIT :start, :limit");
$user_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$user_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$user_q->execute();

$i = 1;
while ($users = $user_q->fetch(PDO::FETCH_ASSOC)) {
    $class = ($i % 2 == 0) ? 'list2' : 'list';
    echo '<div class="' . $class . '">';
    echo user_list($users['id']);
?>
    <span style="float: right;">
        <?php echo ($users['power'] + $users['block'] + $users['health']); ?>
    </span>
</div>
<?php
    $i++;
}

ppage($url, $num, $str, $result);
foot($uid);
?>