<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--child') {
    $case = $argv[2];
    $stored = $argv[3];
    $user = ['adm' => $case === 'non-admin' ? 0 : 3];
    $uid = str_starts_with($case, 'landing') || str_starts_with($case, 'entry-no-consent') ? 0 : 1;
    $queries = [];
    $notice = '';
    $_SESSION = ['dragon_csrf' => 'test-token'];
    $_SERVER['REQUEST_METHOD'] = in_array($case, ['save-on', 'save-off', 'invalid', 'bad-token', 'non-admin'], true) ? 'POST' : 'GET';
    $_GET = match ($case) {
        'landing-help' => ['help' => '1'],
        'landing-login' => ['login' => '1'],
        'landing-password' => ['password' => '1'],
        'landing-reset' => ['id' => '1', 'hash' => 'test'],
        'entry-no-consent-start' => ['start' => '1'],
        'entry-no-consent-login' => ['login' => '1', 'enter' => ''],
        default => $uid ? ['dragon' => '1'] : [],
    };
    $_COOKIE = [];
    $_POST = [
        'csrf_token' => $case === 'bad-token' ? 'wrong' : 'test-token',
        'dragon_enabled' => $case === 'invalid' ? 'invalid' : ($case === 'save-on' ? '1' : '0'),
    ];

    function number($value) { return abs((int)$value); }
    function fch($sql, $params = []) {
        global $stored;
        check($params === ['landing_dragon_enabled'], 'Unexpected setting read');
        return $stored === 'missing' ? false : ['setting_value' => $stored];
    }
    function qry($sql, $params = []) { $GLOBALS['queries'][] = [$sql, $params]; }
    function head($title, $uid, $gameLanding = false) { echo $gameLanding ? '<header data-game-landing></header>' : '<header></header>'; }
    function foot($uid) { echo '<footer></footer>'; }
    function logs() { return ''; }
    function checking($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
    function msg($message) { $GLOBALS['notice'] = $message; }
    function err($message) { $GLOBALS['notice'] = $message; }

    register_shutdown_function(function () {
        fwrite(STDERR, json_encode(['queries' => $GLOBALS['queries'], 'notice' => $GLOBALS['notice']]));
    });

    $file = $uid ? 'update.php' : 'index.php';
    $source = file_get_contents(__DIR__ . '/../' . $file);
    $source = str_replace(
        ['require_once __DIR__ . "/core/func.php";', "require_once __DIR__ . '/core/func.php';", "require_once __DIR__ . '/core/paypal.php';"],
        '',
        $source
    );
    $source = str_replace('__DIR__', var_export(dirname(__DIR__), true), $source);
    eval('?>' . $source);
    exit;
}

function runCase($case, $stored = 'missing') {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--child', $case, $stored], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
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

foreach (['missing' => true, '1' => true, '0' => false] as $setting => $visible) {
    [$output] = runCase('landing', (string)$setting);
    check(str_contains($output, 'class="dragon-sky"') === $visible, "Dragon markup incorrect for $setting");
    check(str_contains($output, 'class="landing-content"') && str_contains($output, 'class="logo"'), 'Landing page missing');
    check(str_contains($output, '<header data-game-landing>'), 'Landing page should request game structured data');
    check(str_contains($output, 'Got An Account - <a href="/?login=1" id="landing-login-button"'), 'Landing login link missing');
    check(str_contains($output, '<a href="/index.php?help=1">How The Mystery Unfolds</a>'), 'Landing help link missing');
    check(!preg_match('/<\/?center\b/i', $output), 'Landing page uses obsolete center markup');
}

foreach (['landing-login', 'landing-password', 'landing-reset'] as $case) {
    [$output] = runCase($case);
    check(!str_contains($output, '<header data-game-landing>'), "$case should not request game structured data");
    check(!preg_match('/<\/?center\b/i', $output), "$case uses obsolete center markup");
    check(str_contains($output, '<div class="cntr">'), "$case is missing its centered wrapper");
    check(preg_match('/<\/div>\s*<footer>/', $output) === 1, "$case centered wrapper is not closed before the footer");
}

[$helpOutput] = runCase('landing-help');
check(str_contains($helpOutput, '<a href="/" class="tophead cntr">Back to Home</a>'), 'Guest help page missing home link');
check(str_contains($helpOutput, '<a href="/privacy.php" class="tophead">Privacy Policy</a>'), 'Privacy Policy link missing from help page');
$privacyPosition = strpos($helpOutput, '>Privacy Policy</a>');
$royalPosition = strpos($helpOutput, '>Royal Tournament Guide</a>');
$termsPosition = strpos($helpOutput, '>Terms of Service &amp; User Agreement</a>');
check($privacyPosition !== false && $royalPosition !== false && $termsPosition !== false && $privacyPosition < $royalPosition && $royalPosition < $termsPosition, 'Helpful links are not in alphabetical order');
$homePosition = strpos($helpOutput, '<a href="/" class="tophead cntr">Back to Home</a>');
$termsLabelPosition = strpos($helpOutput, '>Terms of Service &amp; User Agreement</a>');
check($homePosition !== false && $termsLabelPosition !== false && $homePosition > $termsLabelPosition, 'Back to Home link is not at the bottom of the guest help page');

foreach (['entry-no-consent-start', 'entry-no-consent-login'] as $case) {
    [$output, $result] = runCase($case);
    check($output === '', "$case rendered content instead of requiring consent first");
    check($result['queries'] === [], "$case reached game-entry logic without consent");
}

foreach (['save-off' => '0', 'save-on' => '1'] as $case => $expected) {
    [, $result] = runCase($case);
    check(count($result['queries']) === 1 && $result['queries'][0][1] === ['landing_dragon_enabled', $expected], "$case did not save setting");
    check(str_contains($result['notice'], 'saved'), "$case did not report success");
}

foreach (['invalid', 'bad-token', 'non-admin'] as $case) {
    [, $result] = runCase($case);
    check($result['queries'] === [], "$case saved a setting");
    if ($case !== 'non-admin') check($result['notice'] !== '', "$case did not report an error");
}

foreach (['missing' => '1', '0' => '0'] as $setting => $selected) {
    [$output] = runCase('admin', (string)$setting);
    check(str_contains($output, 'value="' . $selected . '" selected'), "Incorrect selected option for $setting");
    check(str_contains($output, 'name="csrf_token"'), 'CSRF token missing');
}

echo "Landing dragon settings tests passed\n";
