<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) != 3) {
    header('Location: /');
    exit();
}

require_once __DIR__ . '/core/paypal.php';

$r = rand(100000, 999999);
$news = number($_GET['news'] ?? 0);

if (isset($_GET['paypal'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!paypalVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
                throw new InvalidArgumentException('Your session expired. Reload the page and try again.');
            }

            $mode = (string)($_POST['paypal_mode'] ?? '');
            if (!in_array($mode, ['sandbox', 'live'], true)) {
                throw new InvalidArgumentException('Select a valid PayPal environment.');
            }
            $clientId = trim((string)($_POST['paypal_client_id'] ?? ''));
            if (strlen($clientId) > 255) {
                throw new InvalidArgumentException('PayPal Client ID is too long.');
            }
            $packs = paypalNormalizePacks($_POST['packs'] ?? null);

            $storedSecret = paypalSetting('paypal_client_secret');
            if (!empty($_POST['clear_paypal_secret'])) {
                $storedSecret = '';
            }
            $newSecret = trim((string)($_POST['paypal_client_secret'] ?? ''));
            if ($newSecret !== '') {
                if (strlen($newSecret) > 500) {
                    throw new InvalidArgumentException('PayPal Secret is too long.');
                }
                $storedSecret = paypalEncryptSecret($newSecret);
            } elseif ($storedSecret !== '') {
                paypalDecryptSecret($storedSecret);
            }

            $encodedPacks = json_encode($packs, JSON_THROW_ON_ERROR);
            global $BaseConnect;
            $BaseConnect->beginTransaction();
            try {
                paypalSaveSetting('paypal_client_id', $clientId);
                paypalSaveSetting('paypal_client_secret', $storedSecret);
                paypalSaveSetting('paypal_mode', $mode);
                paypalSaveSetting('paypal_ruby_packs', $encodedPacks);
                $BaseConnect->commit();
            } catch (Throwable $e) {
                if ($BaseConnect->inTransaction()) {
                    $BaseConnect->rollBack();
                }
                throw $e;
            }
            msg('PayPal settings saved.');
        } catch (InvalidArgumentException | RuntimeException $e) {
            err($e->getMessage());
        } catch (JsonException $e) {
            error_log('Could not encode PayPal Ruby packs: ' . $e->getMessage());
            err('Could not save Ruby pack settings.');
        } catch (PDOException $e) {
            error_log('Could not save PayPal settings: ' . $e->getMessage());
            err('Could not save PayPal settings. Check server logs.');
        }
        header('Location: /update.php?paypal=1&r=' . $r);
        exit();
    }

    $paypalClientId = paypalSetting('paypal_client_id');
    $paypalMode = paypalSetting('paypal_mode', 'sandbox');
    $paypalSecretConfigured = paypalSetting('paypal_client_secret') !== '';
    $appKeyConfigured = strlen((string)env('APP_KEY', '')) >= 32;
    $paypalPacks = paypalLoadPacks();
    $headTitle = 'PayPal Settings - Admin Panel';
    head($headTitle, $uid);
?>
    <form action="?paypal=1" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(paypalCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
        <div class="tophead">
            <b>PayPal Checkout Settings</b><br>
            <small>Enter the REST API credentials from your PayPal Developer application. Keep APP_KEY in the server's .env file and use sandbox credentials for testing.</small><br><br>
            <?php if (!$appKeyConfigured) { ?>
                <span class="red">APP_KEY is missing or too short in .env. Set a random key of at least 32 characters before saving a PayPal Secret.</span><br><br>
            <?php } ?>
            Environment:<br>
            <select name="paypal_mode">
                <option value="sandbox" <?php echo $paypalMode === 'sandbox' ? 'selected' : ''; ?>>Sandbox (testing)</option>
                <option value="live" <?php echo $paypalMode === 'live' ? 'selected' : ''; ?>>Live</option>
            </select><br><br>
            PayPal Client ID:<br>
            <input type="text" name="paypal_client_id" maxlength="255" value="<?php echo htmlspecialchars($paypalClientId, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"><br><br>
            PayPal Secret: <?php echo $paypalSecretConfigured ? '<span class="lime">Configured</span>' : '<span class="red">Not configured</span>'; ?><br>
            <input type="password" name="paypal_client_secret" maxlength="500" value="" autocomplete="new-password" placeholder="Leave blank to keep the saved secret" <?php echo !$appKeyConfigured ? 'disabled' : ''; ?>><br>
            <?php if ($paypalSecretConfigured) { ?>
                <label><input type="checkbox" name="clear_paypal_secret" value="1"> Clear saved PayPal Secret</label><br>
            <?php } ?>
        </div>
        <div class="tophead">
            <b>Ruby Packs (USD)</b>
            <?php foreach ($paypalPacks as $index => $pack) { ?>
                <div class="tophead2">
                    <b>Pack <?php echo $index + 1; ?></b><br>
                    Name:<br>
                    <input type="text" name="packs[<?php echo $index; ?>][name]" maxlength="50" required value="<?php echo htmlspecialchars($pack['name'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                    Rubies:<br>
                    <input type="number" name="packs[<?php echo $index; ?>][rubies]" min="1" max="1000000" required value="<?php echo (int)$pack['rubies']; ?>"><br>
                    Price (USD):<br>
                    <input type="number" name="packs[<?php echo $index; ?>][price]" min="0.01" max="99999.99" step="0.01" required value="<?php echo htmlspecialchars($pack['price'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                    VIP days:<br>
                    <input type="number" name="packs[<?php echo $index; ?>][vip_days]" min="0" max="3650" required value="<?php echo (int)$pack['vip_days']; ?>">
                </div>
            <?php } ?>
            <input type="submit" value="Save PayPal Settings">
        </div>
    </form>
    <a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>
<?php
    foot($uid);
    exit();
}

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
<a href="/update.php?paypal=1" class="tophead">PayPal & Ruby Pack Settings</a>
<a href="/history_ban.php" class="tophead">Moderation & Ban List</a>
<a href="/bot_create.php" class="tophead">Spawn NPC / AI Bot</a>

<?php
foot($uid);
?>