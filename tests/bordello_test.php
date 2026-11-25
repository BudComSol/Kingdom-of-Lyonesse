<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--request') {
    ob_start();
    $BaseConnect = new PDO('sqlite:' . $argv[2], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $uid = (int)$argv[3];
    $stmt = $BaseConnect->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION = ['bordello_csrf' => 'valid'];
    $_SERVER['REQUEST_METHOD'] = $argv[4] === 'view' ? 'GET' : 'POST';
    $_POST = ['csrf_token' => $argv[4] === 'bad-token' ? 'invalid' : 'valid', 'action' => $argv[5] ?? '', 'secret' => $argv[6] ?? ''];
    function head($title, $uid) { echo '<main>'; }
    function foot($uid) { echo '</main>'; }
    function msg($message) { echo $message; }
    function err($message) { echo $message; }
    $source = file_get_contents(__DIR__ . '/../bordello.php');
    $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
    $source = str_replace("__DIR__ . '/core/bordello.php'", var_export(__DIR__ . '/../core/bordello.php', true), $source);
    eval('?>' . $source);
    exit;
}

function request($path, $userId, $case, $action = '', $secret = '', $expectErrorLog = false) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--request', $path, (string)$userId, $case, $action, $secret],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), 'Could not start request');
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "Request failed: $errors");
    check($expectErrorLog ? str_contains($errors, 'Bordello purchase failed:') : $errors === '', "Unexpected request errors: $errors");
    return $output;
}

function fch($sql, $params = []) {
    return ['pieces' => $GLOBALS['setPieces']];
}

$source = file_get_contents(__DIR__ . '/../core/func.php');
$start = strpos($source, 'function effectiveCombatStats(');
$end = strpos($source, 'function clanRewardAmount(', $start);
eval(substr($source, $start, $end - $start));

$setPieces = 4;
$player = ['id' => 1, 'power' => 100, 'block' => 30, 'health' => 100, 'morale_until' => time() + 60];
$stats = effectiveCombatStats($player);
check($stats['power'] === 116 && $stats['health'] === 110 && $stats['block'] === 30, 'Morale or set bonus was applied incorrectly');
$player['morale_until'] = time() - 1;
check(effectiveCombatStats($player)['power'] === 110, 'Expired morale still applies');
unset($player['morale_until']);
check(effectiveCombatStats($player)['power'] === 110, 'Missing morale changed existing stats');

$path = tempnam(sys_get_temp_dir(), 'bordello-test-');
try {
    $db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, silver INTEGER NOT NULL, morale_until INTEGER NOT NULL DEFAULT 0);
        INSERT INTO users VALUES (1, 200, 0), (2, 20, 0);
        CREATE TABLE bordello_secrets (user_id INTEGER NOT NULL, secret_id TEXT NOT NULL, unlocked_at INTEGER NOT NULL, PRIMARY KEY (user_id, secret_id))');
    $view = request($path, 1, 'view');
    check(str_contains($view, 'Mara') && str_contains($view, 'Seek Company') && !str_contains($view, 'A merchant boasting over wine'), 'Locked secret or companion display incorrect');
    check(str_contains(request($path, 1, 'bad-token', 'company', 'gate'), 'session has expired'), 'Invalid CSRF token accepted');
    check(str_contains(request($path, 1, 'post', 'company', 'unknown'), 'not available'), 'Invalid companion accepted');
    check((int)$db->query('SELECT silver FROM users WHERE id = 1')->fetchColumn() === 200, 'Invalid request spent Coins');
    check(str_contains(request($path, 2, 'post', 'company', 'gate'), 'need 100 Coins'), 'Insufficient funds accepted for company');
    check(str_contains(request($path, 2, 'post', 'secret', 'gate'), 'need 50 Coins'), 'Insufficient funds accepted for secret');
    request($path, 1, 'post', 'company', 'gate');
    $user = $db->query('SELECT * FROM users WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    check((int)$user['silver'] === 100 && (int)$user['morale_until'] >= time() + 3590, 'Company did not apply the paid morale bonus');
    check(str_contains(request($path, 1, 'post', 'company', 'bell'), 'no active morale'), 'Active morale was repurchased');
    check((int)$db->query('SELECT silver FROM users WHERE id = 1')->fetchColumn() === 100, 'Cooldown spent Coins');
    check(str_contains(request($path, 1, 'view'), 'Morale active'), 'Active morale not displayed');
    request($path, 1, 'post', 'secret', 'gate');
    check((int)$db->query('SELECT silver FROM users WHERE id = 1')->fetchColumn() === 50, 'Secret was not charged');
    check(str_contains(request($path, 1, 'view'), 'A merchant boasting over wine'), 'Purchased secret not visible');
    check(!str_contains(request($path, 2, 'view'), 'A merchant boasting over wine'), 'Secret leaked to another player');
    check(str_contains(request($path, 1, 'post', 'secret', 'gate'), 'already know'), 'Duplicate secret purchase accepted');
    check((int)$db->query('SELECT silver FROM users WHERE id = 1')->fetchColumn() === 50, 'Duplicate secret purchase was not rolled back');
    check(str_contains(request($path, 1, 'post', 'secret', 'missing'), 'not available'), 'Invalid secret accepted');
    $db->exec("CREATE TRIGGER reject_secret BEFORE INSERT ON bordello_secrets BEGIN SELECT RAISE(ABORT, 'insert failed'); END");
    check(str_contains(request($path, 1, 'post', 'secret', 'bell', true), 'could not be completed'), 'Database failure not reported');
    check((int)$db->query('SELECT silver FROM users WHERE id = 1')->fetchColumn() === 50, 'Failed secret purchase was not rolled back');
    $db->exec('UPDATE users SET morale_until = 0, silver = 100 WHERE id = 1');
    request($path, 1, 'post', 'company', 'bell');
    check((int)$db->query('SELECT silver FROM users WHERE id = 1')->fetchColumn() === 0, 'Company not available after expiry');
    echo "Bordello tests passed\n";
} finally {
    unlink($path);
}
