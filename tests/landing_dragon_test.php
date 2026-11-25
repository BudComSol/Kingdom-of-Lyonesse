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
    check(str_contains($output, 'class="logo-art-slot"') && str_contains($output, 'class="css-logo" role="img" aria-label="Kingdom of Lyonesse, with a red dragon above gold lettering"'), 'Accessible CSS logo missing');
    check(!str_contains($output, '<img src="/images/logo.webp"'), 'Landing page should not render the image logo');
    check(str_contains($output, '.css-logo .dragon-body {'), 'Scoped CSS logo styles missing');
    check(!preg_match('/^\s*\.dragon-(?:body|eye)\s*[{,]/m', file_get_contents(__DIR__ . '/../style/logo-preview.css')), 'CSS logo styles must not affect the flying dragon');
    check(preg_match('/\.logo-art-slot\s*\{[^}]*width:\s*85%;[^}]*aspect-ratio:\s*320\s*\/\s*213;/s', $output) === 1, 'CSS logo slot must match the original image size');
    check(preg_match('/\.logo-art-slot\s+\.css-logo\s*\{[^}]*position:\s*absolute;[^}]*transform:\s*scaleY\(0\.85\);/s', $output) === 1, 'CSS artwork must fit inside the original logo slot');
    check(str_contains($output, 'class="dragon-snort"'), 'CSS logo breath missing');
    check(str_contains($output, '<header data-game-landing>'), 'Landing page should request game structured data');
    check(preg_match('/\.landing-content\s*\{\s*gap:\s*8px;?\s*\}/', $output) === 1, 'Front page should have an 8px gap between the logo and Play As Guest button');
    check(preg_match('/\.landing-content\s*>\s*\.enter_button\s*\{\s*width:\s*min\(100%,\s*270px\);\s*height:\s*48px;?\s*\}/', $output) === 1, 'Play As Guest button should match the oval links width and height');
    check(preg_match('/\.landing-content\s*>\s*\.landing-links\s*\{[^}]*width:\s*min\(100%,\s*270px\);[^}]*margin:\s*40px\s+0\s+0;[^}]*font-size:\s*clamp\(0\.68rem,\s*2\.7vw,\s*0\.78rem\);[^}]*white-space:\s*nowrap;/s', $output) === 1, 'Front page oval links should have a 48px gap below the button and remain 270px wide with smaller, single-line text');
    check(preg_match('/\.landing-content\s*>\s*\.landing-links\s*\{[^}]*border:\s*2px\s+solid/s', $output) === 1, 'Front page oval links should have a 2px border');
    check(!preg_match('/\.landing-content\s*>\s*\.landing-links\s*\{[^}]*letter-spacing:/s', $output), 'Front page oval links should not set letter spacing');
    check(str_contains($output, '0 0 18px var(--gold-glow)'), 'Front page oval should have a gold border glow');
    check(str_contains($output, '<a class="enter_button" href="/?start=1">Play As Guest</a>'), 'Landing page CSS battle button missing');
    check(!str_contains($output, 'button_green.webp'), 'Landing page should not use the battle button image');
    $stylesheet = file_get_contents(__DIR__ . '/../style/style.css');
    $minifiedStylesheet = file_get_contents(__DIR__ . '/../style/style.min.css');
    foreach ([$stylesheet, $minifiedStylesheet] as $buttonStyles) {
        check(str_contains($buttonStyles, '.landing-content > .enter_button::before') && str_contains($buttonStyles, '.landing-content > .enter_button::after'), 'CSS battle button frame missing');
    }
    check(str_contains($output, 'Got An Account - <a href="/?login=1" id="landing-login-button"'), 'Landing login link missing');
    check(preg_match('~<h1 class="landing-help">\s*<a href="/index.php\?help=1">Kingdom Of Lyoness Story</a>\s*</h1>~', $output) === 1, 'Landing story heading or help link missing');
    check(preg_match('~<div class="landing-links">\s*<div>Got An Account.*?</div>\s*<h1 class="landing-help">.*?Kingdom Of Lyoness Story</a>\s*</h1>\s*</div>~s', $output) === 1, 'Landing links should share one oval banner without generic title styles');
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
