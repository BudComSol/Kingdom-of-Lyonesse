<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) != 3) {
    header('Location: /');
    exit();
}

$r = rand(100000, 900000);
$bot = number($_GET['bot'] ?? 0);

if ($bot == 1) {
    $rou = rand(100000, 999999);
    $rous = (string)rand(100000, 999999);
    $passwordHash = hashPassword($rous);
    $hash = md5(md5((string)$rou));
    $botLogin = 'Knight_' . $rou;
    $ip = '127.0.0.1';

    qry("INSERT INTO `users` SET
        `login` = ?,
        `password` = ?,
        `hash_password` = ?,
        `time_reg` = ?,
        `time_on` = ?,
        `ip` = ?,
        `class` = ?,
        `sex` = ?,
        `start` = ?",
        array(
            $botLogin,
            $passwordHash,
            $hash,
            time(),
            time() + 86400 * 365 * 5,
            $ip,
            1,
            1,
            1
        )
    );

    msg("NPC Bot created: " . $botLogin);
    header("Location: ?r=" . $r);
    exit();
}

head("Admin Panel - Bot Creator", $uid);
?>

<div class="tophead cntr">
    <div class="hrs"></div>
    <a href="?bot=1" class="button">Create NPC Bot</a>
    <div class="hrs"></div>
</div>
<a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>

<?php
foot($uid);
?>