<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) != 3) {
    header('Location: /');
    exit();
}

$r = rand(100000, 900000);
$submittedBotName = $_POST['bot_name'] ?? '';
$botName = is_string($submittedBotName) ? trim($submittedBotName) : '';

if (empty($_SESSION['bot_create_csrf'])) {
    $_SESSION['bot_create_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals($_SESSION['bot_create_csrf'], $token)) {
        err('Your session expired. Reload the page and try again.');
    } elseif (!is_string($submittedBotName)) {
        err('Enter a valid bot name.');
    } elseif (strlen($botName) > 300) {
        err('Bot name is too long.');
    } else {
        $rou = rand(100000, 999999);
        if ($botName === '') {
            do {
                $rou = rand(100000, 999999);
                $botLogin = 'Knight_' . $rou;
                $existingBot = fch("SELECT `id` FROM `users` WHERE `login` = ? LIMIT 1", array($botLogin));
            } while ($existingBot);
        } else {
            $botLogin = $botName;
            $existingBot = fch("SELECT `id` FROM `users` WHERE `login` = ? LIMIT 1", array($botLogin));
        }

        if ($existingBot) {
            err('That bot name is already in use. Choose a unique name.');
        } else {
            $rous = (string)rand(100000, 999999);
            $passwordHash = hashPassword($rous);
            $hash = md5(md5((string)$rou));

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
                    '127.0.0.1',
                    1,
                    1,
                    1
                )
            );

            msg('NPC Bot created: ' . htmlspecialchars($botLogin, ENT_QUOTES, 'UTF-8'));
            header("Location: ?r=" . $r);
            exit();
        }
    }
}

head("Admin Panel - Bot Creator", $uid);
?>

<form action="/bot_create.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['bot_create_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
    <div class="tophead cntr">
        <label for="bot_name">Agent name (optional):</label><br>
        <input type="text" id="bot_name" name="bot_name" maxlength="300" value="<?php echo htmlspecialchars($botName, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Leave blank for a default Knight name"><br><br>
        <button type="submit" class="button">Create NPC Bot</button>
    </div>
</form>
<a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>

<?php
foot($uid);
?>
