<?php

function number($value) { return abs((int)$value); }
function effectiveCombatStats($user) { return ['power' => 120, 'block' => 10, 'health' => 100, 'critical_chance' => 0]; }
function clanRewardAmount($uid, $amount) { return $amount; }
function head($title, $uid) { echo '<header>Combat header</header>'; }
function foot($uid) { echo '<footer>Combat footer</footer>'; }
function err2($message) { $GLOBALS['notice'] = $message; }
function msg2($message) { $GLOBALS['notice'] = $message; }
function qry($sql, $params = []) { $GLOBALS['queries'][] = $sql; }

function fch($sql, $params = []) {
    global $case, $fight;
    if (str_contains($sql, 'FROM `lec_user`')) return ['level' => 1];
    if (str_contains($sql, 'FROM `lec_fight`')) return $fight;
    if (str_contains($sql, 'FROM `skills`') && str_contains($sql, 'AND `id`')) {
        if ($case === 'full-health') return ['type' => 6, 'param' => 10];
        if ($case === 'skill') return ['id' => 1, 'type' => 1, 'param' => 10, 'reset_max' => 5, 'time_max' => 5];
        return false;
    }
    if ($case === 'expired' && str_contains($sql, "`type` = '2'")) {
        return ['id' => 2, 'reset' => 0, 'fight' => 1];
    }
    return false;
}

if (($argv[1] ?? '') === '--child') {
    $case = $argv[2];
    $uid = 1;
    $user = ['id' => 1, 'login' => 'Knight'];
    $fight = [
        'id' => 1, 'id_bot' => 1, 'name' => 'Monster', 'type' => 'metal',
        'bot_power' => 12, 'bot_block' => 10, 'bot_hp' => 1000, 'power' => 120,
        'block' => 10, 'hp' => 100, 'default' => 1000, 'default2' => 120,
        's2' => 1, 'fight_time' => 0,
    ];
    $_GET = [];
    if (in_array($case, ['strike', 'victory', 'defeat', 'cooldown'], true)) $_GET['attack_get'] = '';
    if (in_array($case, ['invalid-skill', 'full-health', 'skill'], true)) $_GET['skill'] = 1;
    if ($case === 'victory') $fight['bot_hp'] = 1;
    if ($case === 'defeat') { $fight['hp'] = 1; $fight['bot_power'] = 1200; }
    if ($case === 'cooldown') $fight['fight_time'] = time() + 10;
    if ($case === 'full-health') $fight['hp'] = 120;
    if ($case === 'expired') $fight['bot_power'] = 11;
    $queries = [];
    $notice = '';
    register_shutdown_function(function () {
        fwrite(STDERR, "\nRESULT:" . json_encode([
            'status' => http_response_code(),
            'headers_sent' => headers_sent(),
            'notice' => $GLOBALS['notice'],
            'queries' => $GLOBALS['queries'],
        ]) . "\n");
    });

    $source = file_get_contents(__DIR__ . '/../lec.php');
    $source = str_replace('require_once __DIR__ . "/core/func.php";', '', $source);
    $source = str_replace('__DIR__ . "/templates/fight_lec.php"', var_export(__DIR__ . '/../templates/fight_lec.php', true), $source);
    eval('?>' . $source);
    exit;
}

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

foreach (['strike', 'victory', 'defeat', 'cooldown', 'invalid-skill', 'full-health', 'skill', 'expired', 'view'] as $case) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', '-d', 'output_buffering=0', __FILE__, '--child', $case],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start $case");
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "$case failed: $errors");
    check(preg_match('/RESULT:(\{.*\})/', $errors, $matches) === 1, "$case missing result: $errors");
    $result = json_decode($matches[1], true);
    check(is_array($result), "$case returned invalid result");
    check(!str_contains($errors, 'Warning') && !str_contains($errors, 'Fatal error'), "$case emitted an error: $errors");
    if ($case === 'view') {
        check(str_contains($output, '<header>Combat header</header>'), 'Combat header missing');
        check(str_contains($output, 'URL=lec.php?attack_get'), 'Automatic attack refresh missing');
        check(str_contains($output, 'id="monster-card"') && str_contains($output, '<footer>Combat footer</footer>'), 'Combat view incomplete');
        continue;
    }

    check($result['status'] === 302 && !$result['headers_sent'] && $output === '', "$case did not redirect before output: $errors $output");
    if ($case === 'strike') check(count($result['queries']) === 2, 'Strike did not update combatants');
    if ($case === 'victory') check(str_contains($result['notice'], 'Victory!'), 'Victory notice missing');
    if ($case === 'defeat') check(str_contains($result['notice'], 'Defeat!'), 'Defeat notice missing');
    if ($case === 'cooldown') check(str_contains($result['notice'], 'once every 2 seconds'), 'Cooldown notice missing');
    if ($case === 'full-health') check(str_contains($result['notice'], 'health is already full'), 'Full health notice missing');
    if ($case === 'skill') check(count($result['queries']) === 2, 'Skill was not applied');
    if ($case === 'expired') check(count($result['queries']) === 2, 'Expired skill was not cleared');
}
echo "Forest combat redirect tests passed\n";
