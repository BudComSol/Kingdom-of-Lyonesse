<?php

require_once __DIR__ . '/../core/install.php';
$root = dirname(__DIR__);
ini_set('display_errors', '0');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; script-src 'self'; img-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

if (installationConfigured($root)) {
    header('Location: /');
    exit;
}

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$requirements = installerRequirements($root);
$ready = !in_array(false, $requirements, true);
$error = extension_loaded('session') ? '' : 'Enable the PHP session extension to continue.';
$csrfToken = '';
if (extension_loaded('session')) {
    session_name('LYONESSE_INSTALL');
    if (!session_start(['cookie_httponly' => true, 'cookie_secure' => $secure, 'cookie_samesite' => 'Strict', 'use_strict_mode' => true, 'use_only_cookies' => true])) {
        $ready = false;
        $error = 'Unable to start a setup session. Check the PHP session storage configuration.';
    } else {
        if (empty($_SESSION['install_token'])) {
            $_SESSION['install_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['install_token'];
    }
}
$success = false;
$values = ['DB_HOST' => 'localhost', 'DB_PORT' => '3306', 'DB_NAME' => '', 'DB_USER' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $default) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) {
            $values[$key] = $_POST[$key];
        }
    }
    $token = $_POST['csrf_token'] ?? '';
    if ($csrfToken === '' || !is_string($token) || !hash_equals($csrfToken, $token)) {
        http_response_code(403);
        $error = 'Your setup session could not be verified. Reload the page and try again.';
    } elseif (!$ready) {
        http_response_code(422);
        $error = 'Resolve the server requirements below before installing.';
    } else {
        $lock = null;
        $database = null;
        $databaseLocked = false;
        $importStarted = false;
        try {
            $settings = installerSettings($_POST);
            $lock = fopen($root . '/.env.install.lock', 'c');
            if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Another setup is running, or the configuration directory cannot be locked. Try again shortly.');
            }
            if (installationConfigured($root)) {
                throw new RuntimeException('The game has already been configured. Reload to enter the kingdom.');
            }
            $database = new PDO(
                "mysql:host={$settings['DB_HOST']};port={$settings['DB_PORT']};dbname={$settings['DB_NAME']};charset=utf8mb4",
                $settings['DB_USER'],
                $settings['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
            );
            $dbLock = $database->prepare('SELECT GET_LOCK(?, 0)');
            $lockName = 'lyonesse-install-' . substr(hash('sha256', $settings['DB_NAME']), 0, 40);
            $dbLock->execute([$lockName]);
            $databaseLocked = (int)$dbLock->fetchColumn() === 1;
            if (!$databaseLocked) {
                throw new RuntimeException('Another installer is using this database. Try again shortly.');
            }
            if ($database->query('SHOW TABLES')->fetchColumn() !== false) {
                throw new RuntimeException('Database not empty, choose a new empty database; as existing game data will not be changed.');
            }
            $sql = file_get_contents($root . '/_sql/database.sql');
            if ($sql === false) {
                throw new RuntimeException('Unable to read the database schema and seed data.');
            }
            $importStarted = true;
            installerImport($database, $sql);
            installerSaveConfiguration($root, $settings, $secure);
            $success = true;
            unset($_SESSION['install_token']);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $error = $exception->getMessage();
        } catch (PDOException $exception) {
            http_response_code(422);
            // Do not expose connection strings, usernames, passwords or seed SQL.
            error_log('Installer database operation failed (SQLSTATE ' . $exception->getCode() . ').');
            $error = $importStarted
                ? 'Database import failed. Check database privileges and MySQL / MariaDB compatibility.'
                : 'Could not connect to the database. Check the host, port, database name, user and password, and ensure the user has database privileges.';
        } catch (RuntimeException $exception) {
            http_response_code(422);
            $error = $exception->getMessage();
        } finally {
            if ($databaseLocked) {
                $release = $database->prepare('SELECT RELEASE_LOCK(?)');
                $release->execute([$lockName]);
            }
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
        if (!$success && $importStarted) {
            $error .= ' Setup was not completed and .env was not saved. The database may contain partial tables: empty only this new setup database or choose another empty database before retrying.';
        }
    }
}

function installEscape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Found Your Kingdom | Kingdom of Lyonesse</title>
    <link rel="stylesheet" href="/install/install.css">
    <script src="/install/install.js" defer></script>
</head>
<body>
<main class="installer">
    <aside class="kingdom">
        <a class="brand" href="/install/"><img src="/images/logo-272.webp" alt="Kingdom of Lyonesse"></a>
        <div class="kingdom-story">
            <p class="eyebrow">A new age begins</p>
            <h1>Every Legend Needs<br>Their Own Kingdom</h1>
            <p>Raise the gates of Lyonesse. Prepare your realm, awaken its world, and let the adventure begin.</p>
            <ol class="steps" aria-label="Installation steps">
                <li><span>01</span><div><strong>Prepare The Realm</strong><small>Check your server requirements</small></div></li>
                <li><span>02</span><div><strong>Lay The Foundations</strong><small>Connect your database and secure your game</small></div></li>
                <li><span>03</span><div><strong>Open The Gates</strong><small>Populate the world and enter Lyonesse</small></div></li>
            </ol>
        </div>
        <p class="realm-note">Your server. Your world. Your story.</p>
    </aside>
    <section class="setup" aria-labelledby="setup-title">
    <?php if ($success): ?>
        <div class="success">
            <div class="seal" aria-hidden="true">&#10003;</div>
            <p class="eyebrow">The realm is ready</p>
            <h2 id="setup-title">Welcome To Your Kingdom.</h2>
            <p>Your database is populated with equipment, monsters, quests and news. Your private configuration and secret key have been saved.</p>
            <div class="notice">Back up <strong>.env</strong> securely. Keep <strong>APP_KEY</strong> unchanged: it protects stored payment credentials. The installer is now locked.</div>
            <p>Enter the game to register your first hero. Administrator promotion is described in the README; no default administrator account is created.</p>
            <a class="primary" href="/">Enter The Kingdom. <span aria-hidden="true">&#8594;</span></a>
        </div>
    <?php else: ?>
        <p class="eyebrow">Kingdom of Lyonesse / First-time setup</p>
        <h2 id="setup-title">Found Your Kingdom</h2>
        <p class="intro">A few details, and your world is ready to explore.</p>
        <?php if ($error !== ''): ?><div class="error" role="alert"><?= installEscape($error) ?></div><?php endif; ?>
        <details class="requirements" <?= !$ready ? 'open' : '' ?>>
            <summary><span>Server Requirements</span><strong class="<?= $ready ? 'pass' : 'fail' ?>"><?= $ready ? 'Ready to install' : 'Action needed' ?></strong></summary>
            <ul><?php foreach ($requirements as $label => $passed): ?>
                <li><span><?= installEscape($label) ?></span><strong class="<?= $passed ? 'pass' : 'fail' ?>"><?= $passed ? 'Ready' : 'Missing' ?></strong></li>
            <?php endforeach; ?></ul>
        </details>
        <form method="post" action="/install/" id="install-form">
            <input type="hidden" name="csrf_token" value="<?= installEscape($csrfToken) ?>">
            <fieldset>
                <legend><span>01</span> Database connection</legend>
                <p class="hint">Create an <strong>empty MySQL 5.7+ / MariaDB 10.3+ database</strong> in your hosting panel first. Give its user full privileges. We'll import the world for you.</p>
                <div class="fields">
                    <label class="host">Database Host<input name="DB_HOST" required value="<?= installEscape($values['DB_HOST']) ?>" placeholder="localhost" autocomplete="off"></label>
                    <label class="port">Port<input name="DB_PORT" type="number" required min="1" max="65535" value="<?= installEscape($values['DB_PORT']) ?>"></label>
                    <label>Database Name<input name="DB_NAME" required value="<?= installEscape($values['DB_NAME']) ?>" placeholder="lyonesse" autocomplete="off"></label>
                    <label>Database User<input name="DB_USER" required value="<?= installEscape($values['DB_USER']) ?>" placeholder="Your database user" autocomplete="off"></label>
                    <label class="full">Database Password<input name="DB_PASS" type="password" placeholder="Your database password (may be blank locally)" autocomplete="new-password"></label>
                </div>
            </fieldset>
            <fieldset>
                <legend><span>02</span> Secure Your Realm</legend>
                <label>Application Secret Key
                    <div class="key-field"><input name="APP_KEY" id="app-key" type="password" minlength="32" placeholder="Leave blank to generate securely" autocomplete="new-password"><button type="button" id="generate-key">Generate</button></div>
                </label>
                <p class="hint">Use a unique random key of at least 32 characters, or let us generate one. It encrypts stored payment credentials.</p>
            </fieldset>
            <div class="notice">Installation creates all tables and imports game data. Existing databases are never overwritten. Use HTTPS and restrict access to this site until setup is finished, then delete install folder.</div>
            <button class="primary" type="submit" <?= !$ready ? 'disabled' : '' ?>>Install &amp; Open The Gates <span aria-hidden="true">&#8594;</span></button>
            <p class="footnote" id="install-status" role="status">Configuration is saved privately in .env. Setup disables itself when complete.</p>
        </form>
    <?php endif; ?>
    </section>
</main>
</body>
</html>
