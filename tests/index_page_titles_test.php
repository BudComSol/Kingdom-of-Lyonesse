<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--child') {
    $uid = 1;
    $user = ['start' => (int)$argv[2], 'login' => 'Knight', 'sex' => '1'];
    $_GET = [];
    $_POST = [];
    $_COOKIE = ['lyonesse_cookie_consent' => 'accepted'];
    $_SESSION = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';

    function number($value) { return abs((int)$value); }
    function qry($sql, $params = []) {}
    function head($title, $uid) { echo '<title>' . $title . '</title>'; }
    function foot($uid) { echo '<footer></footer>'; }
    function logs() { return ''; }

    $source = file_get_contents(__DIR__ . '/../index.php');
    $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
    $source = str_replace('__DIR__', var_export(dirname(__DIR__), true), $source);
    eval('?>' . $source);
    exit;
}

foreach ([
    1 => 'First Memory',
    2 => 'First Encounter',
    3 => 'Battle With The Dragon',
    4 => 'My Time With Elaine',
    5 => 'My Time With Elaine',
    6 => 'My Time With Elaine',
    7 => 'My Time With Elaine',
    8 => 'My Time With Elaine',
    9 => 'My Time With Elaine',
    10 => 'The Town Square',
    11 => 'The Town Square',
] as $step => $title) {
    $process = proc_open([PHP_BINARY, __FILE__, '--child', (string)$step], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start step $step");
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "Step $step failed: $errors");
    check(str_contains($output, '<title>' . $title . '</title>'), "Incorrect title for step $step");
    $content = $step <= 3 ? '/images/start/' . $step . '.png' : ($step === 7 ? 'Hero Name:' : ($step <= 9 ? 'Elaine' : 'Dark Forest'));
    check(str_contains($output, $content), "Incorrect page content for step $step");
}

echo "Index page title tests passed\n";
