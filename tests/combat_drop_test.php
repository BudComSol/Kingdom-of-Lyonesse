<?php

if (($argv[1] ?? '') === '--child') {
    $BaseConnect = new PDO('sqlite:' . $argv[2], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $BaseConnect->sqliteCreateFunction('RAND', fn() => 0);
    $uid = 1;
    $user = ['id' => 1, 'level' => 5, 'health' => 100];
    $_REQUEST = [];
    mt_srand((int)$argv[3]);

    $source = file_get_contents(__DIR__ . '/../api_combat.php');
    $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
    eval('?>' . $source);
    exit;
}

function qry($sql, $params = []) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fch($sql, $params = []) {
    return qry($sql, $params)->fetch(PDO::FETCH_ASSOC);
}

function checking($value) { return $value; }
function number($value) { return (int)$value; }
function effectiveCombatStats($user) { return ['health' => $user['health'], 'critical_chance' => 0]; }
function clanRewardAmount($uid, $amount) { return $amount; }

function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function seedForDrop($wantDrop) {
    for ($seed = 0; $seed < 1000; $seed++) {
        mt_srand($seed);
        rand(90, 150);
        rand(40, 80);
        rand(3, 4);
        rand(1, 1);
        rand(30, 40);
        rand(1, 1);
        rand(1, 100);
        if ((rand(1, 100) <= 35) === $wantDrop) {
            return $seed;
        }
    }
    throw new RuntimeException('Could not find a combat random seed');
}

function runCase($wantDrop, $eligibleItem, $failInsert = false) {
    $path = tempnam(sys_get_temp_dir(), 'combat-test-');
    try {
        $db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec("
            CREATE TABLE users (id INTEGER PRIMARY KEY, metal INTEGER, kills_boss INTEGER, exp INTEGER, silver INTEGER, tour INTEGER);
            INSERT INTO users VALUES (1, 0, 0, 0, 0, 0);
            CREATE TABLE lec_user (id_user INTEGER PRIMARY KEY, metal INTEGER);
            INSERT INTO lec_user VALUES (1, 0);
            CREATE TABLE lec_fight (id INTEGER PRIMARY KEY, id_user INTEGER, type TEXT, bot_power INTEGER, bot_block INTEGER, power INTEGER, block INTEGER, bot_hp INTEGER, hp INTEGER, `default` INTEGER);
            INSERT INTO lec_fight VALUES (1, 1, 'metal', 12, 10, 120, 10, 1, 100, 100);
            CREATE TABLE skills (id_user INTEGER, type INTEGER);
            CREATE TABLE items (id INTEGER PRIMARY KEY, `set` INTEGER, name TEXT, type INTEGER, level INTEGER, power INTEGER, block INTEGER, health INTEGER, max_smitch INTEGER);
            CREATE TABLE inv (id INTEGER PRIMARY KEY, id_user INTEGER, `set` INTEGER DEFAULT 0, name TEXT NOT NULL, type INTEGER DEFAULT 0, power INTEGER DEFAULT 0, block INTEGER DEFAULT 0, health INTEGER DEFAULT 0, energy INTEGER NOT NULL, max_smitch INTEGER DEFAULT 0, smitch INTEGER DEFAULT 0, `yes` INTEGER DEFAULT 0, level INTEGER DEFAULT 0, rune INTEGER DEFAULT 0, rune_param INTEGER DEFAULT 0);
        ");
        $level = $eligibleItem ? 5 : 6;
        $db->exec("INSERT INTO items VALUES (7, 2, 'Warrior Sword', 5, $level, 90, 80, 70, 20)");
        if ($failInsert) {
            $db->exec("CREATE TRIGGER reject_drop BEFORE INSERT ON inv BEGIN SELECT RAISE(ABORT, 'drop failed'); END");
        }

        $process = proc_open([PHP_BINARY, __FILE__, '--child', $path, (string)seedForDrop($wantDrop)],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        check(is_resource($process), 'Could not start combat endpoint');
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        check(proc_close($process) === 0, "Combat process failed: $errors");
        $response = json_decode($output, true);
        check(is_array($response), "Invalid combat response: $output $errors");

        if ($failInsert) {
            check($response['success'] === false && str_contains($errors, 'drop failed'), 'Drop failure was not reported');
            check((int)$db->query('SELECT COUNT(*) FROM lec_fight')->fetchColumn() === 1, 'Fight deletion was not rolled back');
            check((int)$db->query('SELECT kills_boss FROM users')->fetchColumn() === 0, 'Victory rewards were not rolled back');
            check((int)$db->query('SELECT metal FROM lec_user')->fetchColumn() === 0, 'Resource reward was not rolled back');
            return;
        }

        check($response['success'] === true && $response['status'] === 'victory', 'Victory failed');
        check((int)$db->query('SELECT COUNT(*) FROM lec_fight')->fetchColumn() === 0, 'Fight was not completed');
        $item = $db->query('SELECT * FROM inv')->fetch(PDO::FETCH_ASSOC);
        if (!$wantDrop || !$eligibleItem) {
            check($item === false && $response['reward']['item'] === null, 'Unexpected drop');
            return;
        }

        check($item !== false, 'Drop was not inserted');
        foreach (['id_user' => 1, 'set' => 2, 'name' => 'Warrior Sword', 'type' => 5, 'level' => 5,
                  'power' => 90, 'block' => 80, 'health' => 70, 'energy' => 0, 'max_smitch' => 20,
                  'smitch' => 0, 'yes' => 0] as $field => $expected) {
            check($item[$field] == $expected, "Incorrect drop field: $field");
        }
        check($response['reward']['item'] === [
            'name' => 'Warrior Sword', 'quality' => 2, 'icon' => '/images/items/2/5.png'
        ], 'Drop response does not match inventory');
    } finally {
        unlink($path);
    }
}

runCase(true, true);
runCase(true, false);
runCase(false, true);
runCase(true, true, true);
echo "Combat drop tests passed\n";
