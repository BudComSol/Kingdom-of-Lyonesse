<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--child') {
    $case = $argv[2];
    if ($case === 'render') {
        function number($value) { return abs((int)$value); }
        function cnt($sql, $params) { return 4; }
        function head($title, $uid) {}
        function foot($uid) {}
        function page($num, $size, $count) { return 0; }
        function ppage($url, $num, $size, $count) {}
        function user_list($id) { return 'Hero ' . $id; }
        class RenderStatement {
            private $rows;
            public function __construct() {
                $this->rows = [
                    ['id' => 1, 'time_on' => time() - 120, 'session_started' => time() - 3700, 'time_reg' => time() - 86400, 'ip' => '192.0.2.1'],
                    ['id' => 2, 'time_on' => time() - 60, 'session_started' => 0, 'time_reg' => time() - 86400, 'ip' => '127.0.0.1'],
                    ['id' => 3, 'time_on' => time() - 7520 + 86400 * 365 * 5, 'session_started' => 0, 'time_reg' => time() - 7520, 'ip' => '127.0.0.1'],
                    ['id' => 4, 'time_on' => time() + 86400 * 365 * 5, 'session_started' => 0, 'time_reg' => time(), 'ip' => '192.0.2.1'],
                ];
            }
            public function bindValue($name, $value, $type) {}
            public function execute() {}
            public function fetch($mode) { return array_shift($this->rows); }
        }
        class RenderDatabase {
            public function prepare($sql) { return new RenderStatement(); }
        }
        $uid = 1;
        $BaseConnect = new RenderDatabase();
        $source = file_get_contents(__DIR__ . '/../online.php');
        $source = str_replace('require_once __DIR__ . "/core/func.php";', '', $source);
        eval('?>' . $source);
        exit;
    }

    session_start();
    $_SESSION = $case === 'guest' ? [] : ['uid' => 42];
    if ($case === 'existing' || $case === 'logout') {
        $_SESSION['online_session_started'] = time() - 3700;
    }

    class OnlineTestStatement {
        private $sql;
        public function __construct($sql) { $this->sql = $sql; }
        public function execute($params = []) { $GLOBALS['queries'][] = [$this->sql, $params]; }
        public function fetch($mode = null) {
            return ['id' => 42, 'adm' => $GLOBALS['case'] === 'admin' ? 3 : 0];
        }
    }
    class OnlineTestDatabase {
        public function prepare($sql) { return new OnlineTestStatement($sql); }
    }

    $queries = [];
    $BaseConnect = new OnlineTestDatabase();
    $source = file_get_contents(__DIR__ . '/../core/func.php');
    $source = str_replace("require_once __DIR__ . '/../config.php';", '', $source);
    $source = str_replace("__DIR__ . '/item_images.php'", var_export(__DIR__ . '/../core/item_images.php', true), $source);
    $source = str_replace("__DIR__ . '/factions.php'", var_export(__DIR__ . '/../core/factions.php', true), $source);
    $source = substr($source, 0, strpos($source, '// Header Layout'));
    eval('?>' . $source);

    if ($case === 'logout') UserExit($uid);
    if ($case === 'login') UserEnter(42);
    echo json_encode(['queries' => $queries, 'session' => $_SESSION]);
    exit;
}

function runCase($case) {
    $process = proc_open([PHP_BINARY, __FILE__, '--child', $case], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start $case");
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0, "$case failed: $errors");
    $result = json_decode($output, true);
    check(is_array($result), "$case returned invalid result: $output $errors");
    return $result;
}

foreach (['admin', 'player', 'existing'] as $case) {
    $result = runCase($case);
    $update = $result['queries'][1];
    check(str_contains($update[0], '`time_on` = ?, `session_started` = ?'), "$case did not update activity and session");
    check($update[1][2] === 42 && $update[1][1] === $result['session']['online_session_started'], "$case recorded the wrong session");
    $elapsed = time() - $update[1][1];
    check($case === 'existing' ? $elapsed >= 3700 && $elapsed < 3710 : $elapsed < 10, "$case has the wrong session start");
}

$result = runCase('guest');
check($result['queries'] === [], 'Guest updated activity');
$result = runCase('login');
check(time() - $result['session']['online_session_started'] < 10, 'Login did not start a new session');
$result = runCase('logout');
$update = end($result['queries']);
check(str_contains($update[0], '`session_started` = 0') && $update[1][0] === 42 && $update[1][1] > 0, 'Logout did not clear the matching session');
check($result['session'] === [], 'Logout did not clear the session');

$process = proc_open([PHP_BINARY, __FILE__, '--child', 'render'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
check(is_resource($process), 'Could not start render');
$output = stream_get_contents($pipes[1]);
$errors = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
check(proc_close($process) === 0, "Render failed: $errors");
check(str_contains($output, '>1h 1m</span>'), 'Session or activity was rendered incorrectly');
check(preg_match('/Uptime: <span class="session-duration" data-seconds="75[2-4][0-9]">2h 5m<\/span>/', $output) === 1, 'NPC uptime was not rendered from creation');
check(substr_count($output, 'Since: Unavailable') === 2, 'Players without sessions should be shown as Unavailable');

echo "Online session tests passed\n";
