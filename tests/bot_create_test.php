<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--child') {
    $case = $argv[2];
    $user = ['adm' => $case === 'non-admin' ? 0 : 3];
    $uid = 1;
    $queries = [];
    $notice = '';
    $_SESSION = ['bot_create_csrf' => 'test-token'];
    $_SERVER['REQUEST_METHOD'] = in_array($case, ['custom', 'duplicate', 'default', 'bad-token', 'invalid-name', 'non-admin'], true) ? 'POST' : 'GET';
    $_POST = [
        'csrf_token' => $case === 'bad-token' ? 'wrong' : 'test-token',
        'bot_name' => match ($case) {
            'custom' => 'Unique Agent',
            'duplicate' => 'TakenName',
            'invalid-name' => ['not', 'a', 'string'],
            default => '',
        },
    ];
    $GLOBALS['existing_logins'] = $case === 'duplicate' ? ['TakenName'] : [];

    function number($value) { return abs((int)$value); }
    function fch($sql, $params = []) {
        $GLOBALS['queries'][] = [$sql, $params];
        foreach ($GLOBALS['existing_logins'] as $login) {
            if (strcasecmp($login, $params[0]) === 0) return ['id' => 1];
        }
        return false;
    }
    function qry($sql, $params = []) { $GLOBALS['queries'][] = [$sql, $params]; }
    function head($title, $uid) { echo '<header></header>'; }
    function foot($uid) { echo '<footer></footer>'; }
    function hashPassword($password) { return 'hashed:' . $password; }
    function msg($message) { $GLOBALS['notice'] = $message; }
    function err($message) { $GLOBALS['notice'] = $message; }

    register_shutdown_function(function () {
        fwrite(STDERR, json_encode(['queries' => $GLOBALS['queries'], 'notice' => $GLOBALS['notice']]));
    });

    $source = file_get_contents(__DIR__ . '/../bot_create.php');
    $source = str_replace('require_once __DIR__ . "/core/func.php";', '', $source);
    eval('?>' . $source);
    exit;
}

function runCase($case) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--child', $case], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start $case");
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "$case failed: $errors");
    $result = json_decode($errors, true);
    check(is_array($result), "$case returned invalid result: $errors");
    return [$output, $result];
}

[$output] = runCase('render');
check(str_contains($output, 'name="bot_name"') && str_contains($output, 'Leave blank for a default Knight name'), 'Bot name option or default hint missing');
check(str_contains($output, 'name="csrf_token"'), 'CSRF token missing');

[, $result] = runCase('custom');
check(count($result['queries']) === 2, 'Custom bot should check and insert one name');
check($result['queries'][1][1][0] === 'Unique Agent', 'Custom name was not used for bot login');
check(str_contains($result['notice'], 'Unique Agent'), 'Custom bot creation was not reported');

[, $result] = runCase('default');
check(count($result['queries']) === 2, 'Default bot should check and insert one name');
check(str_starts_with($result['queries'][1][1][0], 'Knight_'), 'Blank name did not use the generated default');

[, $result] = runCase('duplicate');
check(count($result['queries']) === 1 && str_contains($result['notice'], 'already in use'), 'Duplicate bot name was not rejected');

foreach (['bad-token', 'invalid-name', 'non-admin'] as $case) {
    [, $result] = runCase($case);
    check($result['queries'] === [], "$case created a bot");
}

echo "Bot creation tests passed\n";
