<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) <= 0) {
    header('Location: /');
    exit();
}

$id = abs(intval($_GET['id'] ?? 0));
$profile = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));

if (!$profile) {
    header("Location: /profile.php?id=" . $id);
    exit();
}

head("Admin - Multi-Account / IP Inspection", $uid);

$count = cnt("SELECT (id) FROM `users` WHERE `id` != ? AND `ip` = ?", array($profile['id'], $profile['ip']));
$str = 10;
$url = "/ip_user.php?id=" . $profile['id'] . "&";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$msg_q = $BaseConnect->prepare("SELECT * FROM `users` WHERE `id` != :pid AND `ip` = :ip ORDER BY `id` DESC LIMIT :start, :limit");
$msg_q->bindValue(':pid', (int)$profile['id'], PDO::PARAM_INT);
$msg_q->bindValue(':ip', $profile['ip'], PDO::PARAM_STR);
$msg_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$msg_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$msg_q->execute();

echo '<div class="tophead cntr">Checking: ' . user_list($profile['id']) . ' <br>Linked IP Accounts Found: ' . $count . '</div>';

if ($count == 0) {
    echo '<div class="tophead2 cntr">No other accounts found sharing this IP address.</div>';
}

while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
    echo '<div class="tophead2">' . user_list($msg['id']) . '<br>IP: ' . htmlspecialchars($msg['ip']) . ' - <a href="/ban_user.php?id=' . $msg['id'] . '"><span class="orange">[Ban Account]</span></a></div>';
}

ppage($url, $num, $str, $result);
?>
<a href="/profile.php?id=<?php echo $profile['id']; ?>" class="tophead cntr">← Back to Hero Profile</a>
<?php
foot($uid);
?>