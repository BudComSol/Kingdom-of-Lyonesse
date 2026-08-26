<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$r = rand(100000, 999999);

if (isset($_GET['id'])) {
    $id = abs(intval($_GET['id']));
    $mail = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));

    if (!$mail || $uid == $mail['id']) {
        header('Location: /contacts.php');
        exit();
    }

    $ignor = cnt("SELECT (id) FROM `black_list` WHERE `id_ank` = ? AND `id_user` = ?", array($uid, $mail['id']));

    qry("UPDATE `contacts_mail` SET `new_msg` = '0' WHERE `id_user` = ? AND `id_ank` = ?", array($uid, $mail['id']));
    qry("UPDATE `mail` SET `check` = '0' WHERE `id_user` = ? AND `id_ank` = ?", array($mail['id'], $uid));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['form'])) {
        if (!empty($_POST)) {
            $text = checking($_POST['text'] ?? '');
            $err = false;

            if ($ban) {
                err("You are banned from sending messages!");
                $err = true;
            }

            if (mb_strlen($text) < 1 || mb_strlen($text) > 5000) {
                $err = true;
            }

            if (($user['spam_message'] ?? 0) > time()) {
                err("You can only send messages once every 3 seconds!");
                $err = true;
            }

            if ($ignor > 0) {
                err("This player has added you to their ignore list.");
                $err = true;
            }

            if ($err == false) {
                qry("INSERT INTO `mail` SET `id_user` = ?, `id_ank` = ?, `time` = ?, `text` = ?", array($uid, $mail['id'], time(), $text));
                qry("UPDATE `users` SET `spam_message` = ? WHERE `id` = ? LIMIT 1", array(time() + 3, $uid));

                $kont = fch("SELECT * FROM `contacts_mail` WHERE `id_user` = ? AND `id_ank` = ? LIMIT 1", array($uid, $mail['id']));
                if (!$kont) {
                    qry("INSERT INTO `contacts_mail` SET `id_user` = ?, `id_ank` = ?, `time` = ?", array($mail['id'], $uid, time()));
                    qry("INSERT INTO `contacts_mail` SET `id_user` = ?, `id_ank` = ?, `time` = ?", array($uid, $mail['id'], time()));
                } else {
                    qry("UPDATE `contacts_mail` SET `time` = ?, `del` = '0' WHERE `id_user` = ? AND `id_ank` = ? LIMIT 1", array(time(), $uid, $mail['id']));
                    qry("UPDATE `contacts_mail` SET `time` = ?, `del` = '0', `new_msg` = '1' WHERE `id_user` = ? AND `id_ank` = ? LIMIT 1", array(time(), $mail['id'], $uid));
                }
            }

            header("Location: /mail.php?id=" . $mail['id']);
            exit();
        }
    }

    head("Conversation with " . htmlspecialchars($mail['login']), $uid);
?>
    <div class="tophead2">
        <center>
            <a class="cntr" onclick="showSmilies(); return false;" href="#"><span class="orange"><u>Emoji Picker</u></span></a>
        </center>
        <div class="hrs"></div>
        <?php require_once __DIR__ . "/templates/smiles.php"; ?>

        <form action="/mail.php?id=<?php echo $mail['id']; ?>&form" method="post">
            <textarea class="mail" name="text" id="message" maxlength="500" required placeholder="Write a message to <?php echo htmlspecialchars($mail['login']); ?>..."></textarea>
            <div class="hrs"></div>
            <center><button type="submit" class="button">Send Mail</button></center>
            <div class="hrs"></div>
        </form>
    </div>

<?php
    $count = cnt("SELECT (id) FROM `mail` WHERE (`id_user` = ? AND `id_ank` = ?) OR (`id_user` = ? AND `id_ank` = ?)", array($mail['id'], $uid, $uid, $mail['id']));
    $str = 10;
    $url = "/mail.php?id=" . $mail['id'] . "&";
    $num = abs(intval($_GET['p'] ?? 1));
    if ($num < 1) $num = 1;
    $result = $count;

    $start = page($num, $str, $result);
    $msg_q = $BaseConnect->prepare("SELECT * FROM `mail` WHERE (`id_user` = :uid1 AND `id_ank` = :mid1) OR (`id_user` = :mid2 AND `id_ank` = :uid2) ORDER BY `time` DESC LIMIT :start, :limit");
    $msg_q->bindValue(':uid1', (int)$uid, PDO::PARAM_INT);
    $msg_q->bindValue(':mid1', (int)$mail['id'], PDO::PARAM_INT);
    $msg_q->bindValue(':mid2', (int)$mail['id'], PDO::PARAM_INT);
    $msg_q->bindValue(':uid2', (int)$uid, PDO::PARAM_INT);
    $msg_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $msg_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
    $msg_q->execute();

    if ($count == 0) {
        echo '<div class="list cntr">No messages in this conversation yet.</div>';
    }

    $i = 1;
    while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
        $class = ($i % 2 == 0) ? 'tophead2' : 'tophead';
        echo '<div class="' . $class . '">';
        echo user_list($msg['id_user']);
?>
        <span style="float: right;">
            <small><?php echo clock($msg['time']); ?></small>
        </span>
        <br>
        <font color="#888">
            <?php echo bbcode($msg['text']); ?>
        </font>
    </div>
<?php
        $i++;
    }

    ppage($url, $num, $str, $result);
?>
    <a href="/contacts.php" class="tophead cntr">← Back to Contacts</a>
<?php
    foot($uid);
    exit();
} else {
    header('Location: /contacts.php');
    exit();
}
?>