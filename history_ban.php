<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$causeReasons = array(
    1 => 'Spam / Advertising',
    2 => 'Profanity / Harassment',
    3 => 'Buying / Selling Accounts',
    4 => 'Buying / Selling Guilds',
    5 => 'Exploiting Bugs / Cheating'
);

$typeBans = array(
    1 => 'Mute / Silence',
    2 => 'Full Account Lock'
);

if (isset($_GET['del']) && ($user['adm'] ?? 0) != 0) {
    $id = abs(intval($_GET['del']));
    $ban_check = fch("SELECT * FROM `ban` WHERE `id` = ? LIMIT 1", array($id));

    if ($ban_check) {
        qry("UPDATE `ban` SET `time` = '0' WHERE `id` = ? LIMIT 1", array($ban_check['id']));
        msg("Penalty lifted successfully!");
    }
    header('Location: /history_ban.php');
    exit();
}

head("Ban History & Moderation Log", $uid);

$count = cnt("SELECT (id) FROM `ban`");
$str = 10;
$url = "/history_ban.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$msg_q = $BaseConnect->prepare("SELECT * FROM `ban` ORDER BY `id` DESC LIMIT :start, :limit");
$msg_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$msg_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$msg_q->execute();

if ($count == 0) {
    echo '<div class="list cntr">No active or recorded bans found.</div>';
}

while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
    if ($msg['time'] > time()) {
        $timeStr = 'Remaining: ' . timer($msg['time']);
        $del_link = ' - <a href="?del=' . $msg['id'] . '"><span class="orange">[Lift Penalty]</span></a>';
    } else {
        $timeStr = 'Ban Expired';
        $del_link = '';
    }

    echo '<div class="tophead">';
    echo 'Banned: ' . user_list($msg['id_user']) . '<br>';
    echo 'Moderator: ' . user_list($msg['adm']) . '<br>';
    echo '<font color="#888"><small>';
    echo 'Reason: ' . ($causeReasons[$msg['cause']] ?? 'Policy Violation') . '<br>';
    echo 'Type: ' . ($typeBans[$msg['tipe']] ?? 'Account Lock') . '<br>';
    echo $timeStr . $del_link;
    echo '</small></font>';
    echo '</div>';
}

ppage($url, $num, $str, $result);
?>
<a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>
<?php
foot($uid);
?>