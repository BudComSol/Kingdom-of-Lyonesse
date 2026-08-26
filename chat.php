<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$r = rand(100000, 999999);

if (($user['chat_check'] ?? 0) != 0) {
    qry("UPDATE `users` SET `chat_check` = '0' WHERE `id` = ? LIMIT 1", array($uid));
    header('Location: /chat.php?r=' . $r);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['form'])) {
    if (!empty($_POST)) {
        $id = abs(intval($_GET['form'] ?? 0));
        $text = checking($_POST['text'] ?? '');
        $err = false;

        if ($ban) {
            err("You are banned from chat!");
            $err = true;
        }

        if (mb_strlen($text) < 1 || mb_strlen($text) > 5000) {
            $err = true;
        }

        if (($user['level'] ?? 1) < 1) {
            err("You need to be higher level to chat!");
            $err = true;
        }

        if (($user['spam_message'] ?? 0) > time()) {
            err("You can only send a message once every 3 seconds!");
            $err = true;
        }

        if ($err == false) {
            qry("INSERT INTO `chat` SET `id_user` = ?, `id_opp` = ?, `time` = ?, `text` = ?", array($uid, $id, time(), $text));
            qry("UPDATE `users` SET `spam_message` = ? WHERE `id` = ? LIMIT 1", array(time() + 3, $uid));
            if ($id > 0) {
                qry("UPDATE `users` SET `chat_check` = '1' WHERE `id` = ? LIMIT 1", array($id));
            }
            header("Location: /chat.php?r=" . $r);
            exit();
        }

        header("Location: /chat.php?r=" . $r);
        exit();
    }
}

if (isset($_GET['del']) && ($user['adm'] ?? 0) != 0) {
    $id = abs(intval($_GET['del']));
    $chat_message = fch("SELECT * FROM `chat` WHERE `id` = ? LIMIT 1", array($id));

    if ($chat_message) {
        $del_check = ($chat_message['del'] == 0) ? 1 : 0;
        qry("UPDATE `chat` SET `del` = ? WHERE `id` = ? LIMIT 1", array($del_check, $chat_message['id']));
    }

    header('Location: /chat.php?r=' . $r);
    exit();
}

head("Global Chat", $uid);
?>

<div class="tophead2 cntr">
    <?php
    if (isset($_GET['user'])) {
        $user_id = abs(intval($_GET['user']));
        $user2 = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($user_id));

        if ($user2 && $user_id != $uid) {
    ?>
            <center>Replying to: <?php echo user_list($user2['id']); ?></center>
            <div class="hrs"></div>
            <form action="/chat.php?form=<?php echo $user_id; ?>" method="post">
    <?php
        } else {
            echo '<form action="/chat.php?form" method="post">';
        }
    } else {
        echo '<form action="/chat.php?form" method="post">';
    }
    ?>
        <a class="cntr" onclick="showSmilies(); return false;" href="#"><span class="orange"><u>Emoji Picker</u></span></a>
        <div class="hrs"></div>
        <?php require_once __DIR__ . "/templates/smiles.php"; ?>
        <textarea class="mail" name="text" id="message" maxlength="500" required placeholder="Type your message here..."></textarea>
        <div class="hrs"></div>
        <center><button type="submit" class="button">Send Message</button></center>
        <div class="hrs"></div>
    </form>
</div>

<?php
$mess = (($user['adm'] ?? 0) <= 0) ? 1 : 2;
$count = cnt("SELECT (id) FROM `chat` WHERE `id_clan` = '0' AND `del` != ?", array($mess));

$str = 10;
$url = "/chat.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$msg_q = $BaseConnect->prepare("SELECT * FROM `chat` WHERE `id_clan` = '0' AND `del` != :mess ORDER BY `id` DESC LIMIT :start, :limit");
$msg_q->bindValue(':mess', $mess, PDO::PARAM_INT);
$msg_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$msg_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$msg_q->execute();

$i = 1;
while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
    $class = ($i % 2 == 0) ? 'tophead2' : 'tophead';
    echo '<div class="' . $class . '">';
    echo user_list($msg['id_user']);

    if ($msg['id_user'] != $uid) {
        echo " <a href='/chat.php?user=" . $msg['id_user'] . "'><img src='/images/icon_reply.png' width='14' height='14' alt='Reply'></a> ";
    }
?>
    <span style="float: right;">
        <small><?php echo clock($msg['time']); ?></small>
    </span>
    <br>
    <?php
    if (($msg['id_opp'] ?? 0) != 0 && $msg['id_opp'] != $uid) {
        echo " <b>" . htmlspecialchars(user($msg['id_opp'], 'login')) . "</b>, ";
    }
    if (($msg['id_opp'] ?? 0) == $uid) {
        echo " <b><font color='lime'>" . htmlspecialchars(user($msg['id_opp'], 'login')) . "</font></b>, ";
    }
    ?>
    <font color="#888">
        <?php echo bbcode($msg['text']); ?>
    </font>

    <?php if (($user['adm'] ?? 0) != 0) { ?>
        <small><div class="right">
            <?php if ($msg['del'] == 0) { ?>
                <a href="/chat.php?del=<?php echo $msg['id']; ?>">[Delete]</a>
            <?php } elseif ($msg['del'] == 1) { ?>
                <a href="/chat.php?del=<?php echo $msg['id']; ?>">[Restore]</a> - <a href="/ban_user.php?id=<?php echo $msg['id_user']; ?>">[Ban]</a>
            <?php } ?>
        </div></small>
    <?php } ?>
</div>
<?php
    $i++;
}

ppage($url, $num, $str, $result);
foot($uid);
?>