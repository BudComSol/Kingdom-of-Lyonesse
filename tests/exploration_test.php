<?php
require_once __DIR__ . '/../core/factions.php';

function qry($sql, $params = []) {
    global $BaseConnect;
    $sql = str_replace(' FOR UPDATE', '', $sql);
    if (preg_match('/^\s*(UPDATE|DELETE)/i', $sql)) {
        $sql = preg_replace('/ LIMIT 1$/', '', $sql);
    }
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
function fch($sql, $params = []) { return qry($sql, $params)->fetch(PDO::FETCH_ASSOC); }
function number($value) { return abs((int)$value); }
function checking($value) { return $value; }
function effectiveCombatStats($user) { return ['power' => 120, 'block' => 10, 'health' => 100, 'critical_chance' => 0]; }
function clanRewardAmount($uid, $amount) { return $amount; }
function err($message) {}
function err2($message) {}
function msg2($message) {}
function head($title, $uid) { echo '<main>'; }
function foot($uid) { echo '</main>'; }

if (($argv[1] ?? '') === '--child') {
    $BaseConnect = new PDO('sqlite:' . $argv[2], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $BaseConnect->sqliteCreateFunction('RAND', fn() => 0);
    $uid = 1;
    $user = fch('SELECT * FROM users WHERE id = ?', [$uid]);
    $_SESSION = ['exploration_csrf' => 'valid'];
    $case = $argv[3];
    if (str_starts_with($case, 'map-')) {
        $_SERVER['REQUEST_METHOD'] = $case === 'map-view' ? 'GET' : 'POST';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = $case === 'map-nojs' ? '' : 'XMLHttpRequest';
        $_POST = ['csrf_token' => $case === 'map-token' ? 'invalid' : 'valid', 'node' => $argv[4] ?? 1];
        $source = file_get_contents(__DIR__ . '/../exploration.php');
        $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
        $source = str_replace("__DIR__ . '/core/exploration.php'", var_export(__DIR__ . '/../core/exploration.php', true), $source);
        eval('?>' . $source);
    } elseif ($case === 'ajax') {
        $_REQUEST = [];
        $source = file_get_contents(__DIR__ . '/../api_combat.php');
        $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
        $source = str_replace("__DIR__ . '/core/exploration.php'", var_export(__DIR__ . '/../core/exploration.php', true), $source);
        eval('?>' . $source);
    } else {
        $_GET = ['attack_get' => ''];
        $lec_fight = fch('SELECT * FROM lec_fight WHERE id_user = ?', [$uid]);
        require __DIR__ . '/../templates/fight_lec.php';
    }
    exit;
}

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

function runRequest($path, $case, $node = 1) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--child', $path, $case, (string)$node],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start $case");
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "$case failed: $errors");
    check(!str_contains($errors, 'Warning') && !str_contains($errors, 'Fatal error'), "$case emitted an error: $errors");
    return $output;
}

$path = tempnam(sys_get_temp_dir(), 'exploration-test-');
try {
    $db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec("
        CREATE TABLE users (id INTEGER PRIMARY KEY, lec INTEGER, level INTEGER, metal INTEGER DEFAULT 0, kills_boss INTEGER DEFAULT 0, exp INTEGER DEFAULT 0, silver INTEGER DEFAULT 0, tour INTEGER DEFAULT 0);
        INSERT INTO users (id, lec, level) VALUES (1, 3, 5), (2, 3, 5);
        CREATE TABLE exploration_progress (user_id INTEGER, node_id INTEGER, discovered_at INTEGER, PRIMARY KEY (user_id, node_id));
        CREATE TABLE lec_user (id INTEGER PRIMARY KEY, id_user INTEGER, level INTEGER, metal INTEGER DEFAULT 0);
        CREATE TABLE lec_boss (id INTEGER PRIMARY KEY, name TEXT, param INTEGER, type TEXT);
        INSERT INTO lec_boss VALUES (1, 'Wild Ram', 40, 'metal'), (2, 'Giant Eagle', 60, 'metal');
        CREATE TABLE lec_fight (id INTEGER PRIMARY KEY, id_user INTEGER, name TEXT, id_bot INTEGER, exploration_node INTEGER DEFAULT 0,
            bot_power INTEGER, bot_block INTEGER, bot_hp INTEGER, power INTEGER, block INTEGER, hp INTEGER, `default` INTEGER, default2 INTEGER,
            type TEXT, fight_time INTEGER DEFAULT 0, clon_hp INTEGER DEFAULT 0);
        CREATE TABLE skills (id INTEGER PRIMARY KEY, id_user INTEGER, type INTEGER, time INTEGER, reset INTEGER);
        CREATE TABLE items (id INTEGER PRIMARY KEY, level INTEGER);
    ");
    $view = runRequest($path, 'map-view');
    check(str_contains($view, 'The Old Gate') && !str_contains($view, 'Eagle Watch'), 'Locked landmarks leaked');
    foreach ([['map-token', 1], ['map-start', 2], ['map-start', 99]] as [$case, $node]) {
        $result = json_decode(runRequest($path, $case, $node), true);
        check($result['success'] === false, "$case accepted an invalid request");
    }
    check((int)$db->query('SELECT lec FROM users WHERE id = 1')->fetchColumn() === 3, 'Rejected requests spent energy');
    $db->exec("CREATE TRIGGER reject_fight BEFORE INSERT ON lec_fight BEGIN SELECT RAISE(ABORT, 'fight failed'); END");
    $result = json_decode(runRequest($path, 'map-start'), true);
    check($result['success'] === false && str_contains($result['message'], 'Could not start'), 'Start failure was not reported');
    check((int)$db->query('SELECT lec FROM users WHERE id = 1')->fetchColumn() === 3, 'Failed start spent energy');
    check((int)$db->query('SELECT count(*) FROM lec_user')->fetchColumn() === 0, 'Failed start did not roll back');
    $db->exec('DROP TRIGGER reject_fight');
    $result = json_decode(runRequest($path, 'map-start'), true);
    check($result['success'] === true && $result['redirect'] === '/lec.php', 'Expedition did not start');
    check((int)$db->query('SELECT lec FROM users WHERE id = 1')->fetchColumn() === 2, 'Expedition did not cost one energy');
    check((int)$db->query('SELECT exploration_node FROM lec_fight')->fetchColumn() === 1, 'Fight is not linked to the landmark');
    $result = json_decode(runRequest($path, 'map-start'), true);
    check($result['success'] === false && str_contains($result['message'], 'current forest battle'), 'Active fight was overwritten');
    $db->exec('UPDATE lec_fight SET bot_hp = 1');
    $result = json_decode(runRequest($path, 'ajax'), true);
    check($result['status'] === 'victory' && $result['exploration'] === true, 'AJAX victory failed');
    check((int)$db->query('SELECT count(*) FROM exploration_progress WHERE user_id = 1 AND node_id = 1')->fetchColumn() === 1, 'Victory did not reveal node');
    check((int)$db->query('SELECT count(*) FROM exploration_progress WHERE user_id = 2')->fetchColumn() === 0, 'Progress leaked between players');
    $view = runRequest($path, 'map-view');
    check(str_contains($view, 'Eagle Watch') && str_contains($view, 'weathered gate') && !str_contains($view, 'Whispering Stones'), 'Map did not reveal the correct content');
    $result = json_decode(runRequest($path, 'map-start', 1), true);
    check($result['success'] === false, 'Completed node was replayed');
    $result = json_decode(runRequest($path, 'map-start', 2), true);
    check($result['success'] === true, 'Next node did not unlock');
    $db->exec('UPDATE lec_fight SET bot_hp = 100000, bot_power = 1000, hp = 1');
    $result = json_decode(runRequest($path, 'ajax'), true);
    check($result['status'] === 'defeat' && $result['exploration'] === true, 'Expedition defeat failed');
    check((int)$db->query('SELECT count(*) FROM exploration_progress')->fetchColumn() === 1, 'Defeat revealed a landmark');
    $db->exec('UPDATE users SET lec = 1 WHERE id = 1');
    $result = json_decode(runRequest($path, 'map-start', 2), true);
    check($result['success'] === true, 'Could not retry after defeat');
    $db->exec('UPDATE lec_fight SET bot_hp = 1');
    runRequest($path, 'legacy');
    check((int)$db->query('SELECT count(*) FROM exploration_progress WHERE node_id = 2')->fetchColumn() === 1, 'Non-AJAX victory did not reveal node');
    $result = json_decode(runRequest($path, 'map-start', 3), true);
    check($result['success'] === false && str_contains($result['message'], 'energy'), 'Expedition started without energy');
    check((int)$db->query('SELECT count(*) FROM lec_fight')->fetchColumn() === 0, 'Rejected expedition created a fight');
    $db->exec('DELETE FROM exploration_progress; UPDATE users SET lec = 1, metal = 0, kills_boss = 0, exp = 0, silver = 0, tour = 0 WHERE id = 1');
    check(json_decode(runRequest($path, 'map-start'), true)['success'], 'Rollback scenario could not start');
    $db->exec("UPDATE lec_fight SET bot_hp = 1; CREATE TRIGGER reject_discovery BEFORE INSERT ON exploration_progress BEGIN SELECT RAISE(ABORT, 'discovery failed'); END");
    $result = json_decode(runRequest($path, 'ajax'), true);
    check($result['success'] === false, 'AJAX discovery failure was not reported');
    check((int)$db->query('SELECT kills_boss FROM users WHERE id = 1')->fetchColumn() === 0, 'AJAX rewards were not rolled back');
    check((int)$db->query('SELECT count(*) FROM lec_fight')->fetchColumn() === 1, 'AJAX fight was not preserved after rollback');
    runRequest($path, 'legacy');
    check((int)$db->query('SELECT kills_boss FROM users WHERE id = 1')->fetchColumn() === 0, 'Non-AJAX rewards were not rolled back');
    check((int)$db->query('SELECT count(*) FROM lec_fight')->fetchColumn() === 1, 'Non-AJAX fight was not preserved after rollback');
    $db->exec('DROP TRIGGER reject_discovery');
    runRequest($path, 'legacy');
    check((int)$db->query('SELECT count(*) FROM exploration_progress WHERE node_id = 1')->fetchColumn() === 1, 'Fight could not be retried after rollback');
    $db->exec('UPDATE users SET lec = 1 WHERE id = 1');
    runRequest($path, 'map-nojs', 2);
    check((int)$db->query('SELECT exploration_node FROM lec_fight')->fetchColumn() === 2, 'Non-JavaScript expedition did not start');
    echo "Exploration tests passed\n";
} finally {
    unlink($path);
}
