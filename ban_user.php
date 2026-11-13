<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) == 0) {
    header('Location: /');
    exit();
}

$id = abs(intval($_GET['id'] ?? 0));
$us = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));

if (!$us) {
    header('Location: /');
    exit();
}

if (isset($_REQUEST['ok'])) {
    $tipe  = abs(intval($_POST['tipe'] ?? 1));
    $cause = abs(intval($_POST['cause'] ?? 1));
    $time  = abs(intval($_POST['time'] ?? 60));
    $err = false;

    $ban_us = fch("SELECT * FROM `ban` WHERE `id_user` = ? AND `time` > ? ORDER BY `id` DESC LIMIT 1", array($us['id'], time()));

    if ($ban_us) {
        err("Player is already banned!");
        $err = true;
    }

    if (($us['adm'] ?? 0) != 0) {
        err("You cannot ban administrators or moderators!");
        $err = true;
    }

    if ($err == false) {
        qry("INSERT INTO `ban` SET `id_user` = ?, `cause` = ?, `tipe` = ?, `adm` = ?, `time` = ?",
            array($us['id'], $cause, $tipe, $uid, time() + ($time * 60)));
        msg("Player successfully banned!");
    }
}

head("Admin - Moderation Ban", $uid);
?>

<div class="tophead2">
    Target Player: <?php echo user_list($us['id']); ?>
    <br><div class="hrs"></div>

    <form action="" method="post">
        Ban Type:
        <select name="tipe">
            <option value="1">Mute / Chat Silence</option>
            <option value="2">Full Account Lock</option>
        </select>
        <br><div class="hrs"></div>

        What For:
        <select name="cause">
            <option value="1">Spam / Advertising</option>
            <option value="2">Profanity / Harassment</option>
            <option value="3">Buying / Selling Accounts</option>
            <option value="4">Buying / Selling Guilds</option>
            <option value="5">Exploiting Bugs / Cheating</option>
        </select>
        <br><div class="hrs"></div>

        Ban Time:
        <select name="time">
            <option value="60">1 Hour</option>
            <option value="120">2 Hours</option>
            <option value="240">4 Hours</option>
            <option value="480">8 Hours</option>
            <option value="960">16 Hours</option>
            <option value="1440">1 Day</option>
            <option value="10080">7 Days</option>
            <option value="44640">1 Month</option>
            <option value="525600">1 Year</option>
            <option value="52560000">Permanent</option>
        </select>
        <br><br>
        <center><input type="submit" name="ok" value="Apply Ban Penalty" /></center>
    </form>
</div>

<a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>

<?php
foot($uid);
?>