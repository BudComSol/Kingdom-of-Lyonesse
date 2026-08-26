<?php
$location = true;
require_once __DIR__ . "/core/func.php";

head("Game News & Announcements", $uid);

if (($user['news'] ?? 0) != 0) {
    qry("UPDATE `users` SET `news` = '0' WHERE `id` = ? LIMIT 1", array($uid));
}

$count = cnt("SELECT (id) FROM `news`");
$str = 5;
$url = "/news.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$msg_q = $BaseConnect->prepare("SELECT * FROM `news` ORDER BY `id` DESC LIMIT :start, :limit");
$msg_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$msg_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$msg_q->execute();

if ($count == 0) {
    echo '<div class="list cntr">No news posted yet.</div>';
}

while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
?>
    <div class="tophead">
        <small>
            <span style="float: left;">
                <img src="/images/topic_closed_0.png" alt="*">
                <b><?php echo htmlspecialchars($msg['name']); ?></b>
            </span>

            <span style="float: right;">
                <img src="/images/date.png" alt="date">
                <?php echo clock($msg['time']); ?>
            </span>

            <br><div class="hrs"></div>

            <font color="gray">
                <?php echo bbcode($msg['text']); ?>
            </font>
        </small>
    </div>
<?php
}

ppage($url, $num, $str, $result);

if (($user['adm'] ?? 0) == 3) {
?>
    <a href="/update.php?news=1" class="tophead">Add News Announcement</a>
<?php
}

foot($uid);
?>