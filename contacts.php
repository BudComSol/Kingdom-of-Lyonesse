<?php
$location = true;
require_once __DIR__ . "/core/func.php";

head("Mail Contacts", $uid);

$count = cnt("SELECT (id) FROM `contacts_mail` WHERE `del` != ? AND `id_user` = ?", array($uid, $uid));
$str = 10;
$url = "/contacts.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$msg_q = $BaseConnect->prepare("SELECT * FROM `contacts_mail` WHERE `del` != :uid1 AND `id_user` = :uid2 ORDER BY `new_msg` DESC, `time` DESC LIMIT :start, :limit");
$msg_q->bindValue(':uid1', (int)$uid, PDO::PARAM_INT);
$msg_q->bindValue(':uid2', (int)$uid, PDO::PARAM_INT);
$msg_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$msg_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$msg_q->execute();

if ($count == 0) {
    echo '<div class="list cntr">You have no messages yet.</div>';
}

$i = 1;
while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
    $check = ($msg['new_msg'] == 0) ? '' : '<font color="lime"> (Unread)</font>';
    $ank = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($msg['id_ank']));
    if (!$ank) continue;

    $message = fch("SELECT * FROM `mail` WHERE (`id_user` = ? AND `id_ank` = ?) OR (`id_user` = ? AND `id_ank` = ?) ORDER BY `id` DESC LIMIT 1",
        array($uid, $ank['id'], $ank['id'], $uid));

    $class = ($i % 2 == 0) ? 'tophead2' : 'tophead';
    echo '<div class="' . $class . '">';
    echo user_list2($msg['id_ank']);
    echo $check;
?>
    <span style="float: right;">
        <small><?php echo clock($message['time'] ?? $msg['time']); ?></small>
    </span>
    <br>
    <font color="#888">
        <a href="/mail.php?id=<?php echo $msg['id_ank']; ?>">
            <?php echo bbcode(text_msg($message['text'] ?? 'Open conversation')); ?>
        </a>
    </font>
</div>
<?php
    $i++;
}

ppage($url, $num, $str, $result);
foot($uid);
?>