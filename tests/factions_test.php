<?php
require_once __DIR__ . '/../core/factions.php';

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--request') {
    $case = $argv[2];
    $uid = 42;
    $user = ['id' => $uid, 'start' => 7, 'login' => 'Knight', 'faction' => 0, 'gold' => 1000];
    $queries = [];
    $notices = [];
    $_SESSION = ['faction_csrf' => 'valid'];
    $_COOKIE = ['lyonesse_cookie_consent' => 'accepted'];
    $_SERVER['REQUEST_METHOD'] = str_ends_with($case, '-view') ? 'GET' : 'POST';
    $_POST = ['login' => 'New Hero', 'faction' => '7', 'csrf_token' => 'valid'];
    if (str_contains($case, 'invalid')) $_POST['faction'] = '8';
    if (str_contains($case, 'missing')) unset($_POST['faction']);
    if (str_contains($case, 'token')) $_POST['csrf_token'] = 'wrong';
    if (str_contains($case, 'low-rubies')) $user['gold'] = 99;
    if (str_contains($case, 'same-faction')) $user['faction'] = 7;
    $_GET = str_starts_with($case, 'onboarding-') && !str_ends_with($case, '-view') ? ['form' => ''] : [];

    function number($value) { return abs((int)$value); }
    function checking($value) { return trim(htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8')); }
    function qry($sql, $params = []) {
        $GLOBALS['queries'][] = [$sql, $params];
        return new class {
            public function rowCount() { return 1; }
        };
    }
    function err($message) { $GLOBALS['notices'][] = $message; }
    function err2($message) { $GLOBALS['notices'][] = $message; }
    function msg($message) { $GLOBALS['notices'][] = $message; }
    function logs() { return ''; }
    function head($title, $uid) { echo '<main>'; }
    function foot($uid) { echo '</main>'; }

    register_shutdown_function(function () {
        fwrite(STDERR, json_encode(['queries' => $GLOBALS['queries'], 'notices' => $GLOBALS['notices']]));
    });
    $file = str_starts_with($case, 'onboarding-') ? 'index.php' : 'factions.php';
    $source = file_get_contents(__DIR__ . '/../' . $file);
    $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
    $source = str_replace('__DIR__', var_export(dirname(__DIR__), true), $source);
    eval('?>' . $source);
    exit;
}

function requestCase($case) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--request', $case], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start $case");
    $html = stream_get_contents($pipes[1]);
    $result = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "$case failed: $result");
    $result = json_decode($result, true);
    check(is_array($result), "Invalid response for $case");
    return [$html, $result];
}

foreach (['onboarding-view', 'faction-view'] as $case) {
    [$html] = requestCase($case);
    foreach (factions() as $id => $faction) {
        check(str_contains($html, '/images/factions/' . $id . '.png') && str_contains($html, $faction['name']), "$case missing faction $id");
    }
    check(str_contains($html, 'name="faction"') && str_contains($html, 'required'), "$case does not require a choice");
}

foreach (['onboarding-valid', 'faction-valid'] as $case) {
    [, $result] = requestCase($case);
    check(count($result['queries']) === 1 && str_contains($result['queries'][0][0], '`faction` = ?'), "$case failed to save faction");
    check(in_array(7, $result['queries'][0][1], true), "$case saved the wrong faction");
}
[, $factionValid] = requestCase('faction-valid');
check(in_array(100, $factionValid['queries'][0][1], true), 'Faction change did not charge 100 Rubies');
check(str_contains($factionValid['queries'][0][0], '`gold` >= ?'), 'Faction change did not enforce the ruby balance');

foreach (['onboarding-invalid', 'onboarding-missing', 'onboarding-token', 'onboarding-duplicate', 'faction-invalid', 'faction-missing', 'faction-token', 'faction-forest', 'faction-tavern', 'faction-low-rubies', 'faction-same-faction'] as $case) {
    [, $result] = requestCase($case);
    check(!$result['queries'] && $result['notices'], "$case was not rejected with a notice");
}

function fch($sql, $params = []) {
    if (!isset($GLOBALS['case'])) return ['pieces' => $GLOBALS['pieces']];
    if (str_contains($GLOBALS['case'], 'forest') && str_contains($sql, 'lec_fight')) return ['id' => 1];
    if (str_contains($GLOBALS['case'], 'tavern') && str_contains($sql, 'tavern_fight')) return ['id' => 1];
    if (str_contains($GLOBALS['case'], 'duplicate') && str_contains($sql, 'SELECT * FROM `users`')) return ['id' => 2];
    return false;
}
$source = file_get_contents(__DIR__ . '/../core/func.php');
$start = strpos($source, 'function effectiveCombatStats(');
$end = strpos($source, 'function clanRewardAmount(', $start);
eval(substr($source, $start, $end - $start));

unset($case);
$pieces = 0;
$hero = ['id' => 1, 'power' => 100, 'block' => 100, 'health' => 100];
check(effectiveCombatStats($hero)['power'] === 100, 'Unaligned heroes should have no bonuses');
check(factionImageUrl($hero) === '/images/no-faction.svg' && factionName($hero) === 'Unaligned', 'Unaligned fallback incorrect');
foreach (factions() as $id => $faction) {
    $hero['faction'] = $id;
    $stats = effectiveCombatStats($hero);
    foreach (['power', 'block', 'health'] as $stat) {
        check($stats[$stat] === 100 + $faction[$stat], "Wrong $stat bonus for $id");
    }
    check(factionImageUrl($hero) === "/images/factions/$id.png", "Wrong image for $id");
}
$pieces = 4;
$hero['faction'] = 1;
$hero['morale_until'] = time() + 60;
$stats = effectiveCombatStats($hero);
check($stats['power'] === 127 && $stats['health'] === 116, 'Faction bonuses should precede set and morale bonuses');
check(factionId(['1']) === 0 && factionId('-1') === 0 && factionId('8') === 0, 'Invalid faction accepted');
echo "Faction tests passed\n";
