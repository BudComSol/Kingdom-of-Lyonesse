<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) != 3) {
    header('Location: /');
    exit();
}

$r = rand(100000, 999999);
$news = number($_GET['news'] ?? 0);

if (isset($_GET['analytics'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $analyticsId = trim((string)($_POST['analytics_id'] ?? ''));
        if ($analyticsId !== '' && !preg_match('/^G-[A-Z0-9]{10}$/i', $analyticsId)) {
            err("Enter a valid GA4 measurement ID (for example, G-XXXXXXXXXX).");
        } else {
            qry(
                "INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)",
                array('google_analytics_id', $analyticsId)
            );
            msg("Google Analytics settings saved.");
        }
        header("Location: /update.php?analytics=1&r=" . $r);
        exit();
    }

    $analyticsSetting = fch(
        "SELECT `setting_value` FROM `site_settings` WHERE `setting_key` = ? LIMIT 1",
        array('google_analytics_id')
    );
    head("Google Analytics - Admin Panel", $uid);
?>
    <form action="?analytics=1" method="post">
        <div class="tophead cntr">
            Google Analytics GA4 Measurement ID:<br>
            <input type="text" name="analytics_id" value="<?php echo htmlspecialchars($analyticsSetting['setting_value'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="12" pattern="G-[A-Za-z0-9]{10}" placeholder="G-XXXXXXXXXX">
            <br><small>Leave blank to disable Google Analytics.</small><br><br>
            <input type="submit" value="Save Analytics Settings">
        </div>
    </form>
    <a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>
<?php
    foot($uid);
    exit();
}

if ($news == 1 && isset($_GET['form'])) {
    $name = checking($_POST['name'] ?? '');
    $text = checking($_POST['text'] ?? '');
    $err = false;

    if (mb_strlen($text) < 1 || mb_strlen($text) > 5000) {
        err("News content is too short or too long!");
        $err = true;
    }

    if ($err == false) {
        qry("INSERT INTO `news` SET `time` = ?, `name` = ?, `text` = ?", array(time(), $name, $text));
        qry("UPDATE `users` SET `news` = '1'");
        msg("Announcement published successfully!");
        header("Location: /update.php?news=1&r=" . $r);
        exit();
    }
}

if ($news == 1) {
    head("Publish Announcement - Admin Panel", $uid);
?>
    <form action="?news=1&form" method="post">
        <div class="tophead cntr">
            Announcement Title:<br><input type="text" name="name" required placeholder="Announcement title..."><br><br>
            Content:<br><textarea class="mail" name="text" id="message" maxlength="5000" required placeholder="Write news announcement..."></textarea>
            <br><br><input type="submit" value="Publish Announcement">
        </div>
    </form>
    <a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>
<?php
    foot($uid);
    exit();
}

head("Game Administration Control Panel", $uid);

$count_ban   = cnt("SELECT (id) FROM `ban`");
$count_mail  = cnt("SELECT (id) FROM `mail`");
$count_news  = cnt("SELECT (id) FROM `news`");
$count_chat  = cnt("SELECT (id) FROM `chat`");
$count_users = cnt("SELECT (id) FROM `users`");

$count_m3  = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 60));
$count_m10 = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 600));
$count_d1  = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 86400));
$count_d31 = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 86400 * 31));
?>

<div class="tophead">
    <b>Online Activity:</b><br>
    Online in past 1 min: <?php echo $count_m3; ?><br>
    Online in past 10 min: <?php echo $count_m10; ?><br>
    Active in past 24 hrs: <?php echo $count_d1; ?><br>
    Active in past 31 days: <?php echo $count_d31; ?><br>
</div>

<div class="tophead">
    <b>Database Metrics:</b><br>
    Registered Heroes: <?php echo $count_users; ?><br>
    Mail Messages: <?php echo $count_mail; ?><br>
    Chat Messages: <?php echo $count_chat; ?><br>
    Announcements: <?php echo $count_news; ?><br>
    Active & Past Bans: <?php echo $count_ban; ?>
</div>

<a href="/create_item.php" class="tophead">Create Equipment Set</a>
<a href="/update.php?news=1" class="tophead">Publish News / Announcement</a>
<a href="/update.php?analytics=1" class="tophead">Google Analytics Settings</a>
<a href="/history_ban.php" class="tophead">Moderation & Ban List</a>
<a href="/bot_create.php" class="tophead">Spawn NPC / AI Bot</a>

<?php
foot($uid);
?>