<?php

if (($argv[1] ?? '') === '--child') {
    class TestStatement {
        public function fetchAll($mode) {
            return ['id_clan', 'title'];
        }
    }

    class TestPDO {
        public function __construct(...$args) {}
        public function exec($sql) {
            if (($GLOBALS['argv'][2] ?? '') === 'fail' && str_contains($sql, 'CREATE TABLE')) {
                throw new PDOException('schema failure');
            }
            $GLOBALS['queries'][] = $sql;
        }
        public function query($sql) {
            $GLOBALS['queries'][] = $sql;
            return new TestStatement();
        }
    }

    $queries = [];
    if (($argv[2] ?? '') !== 'request') {
        define('RUN_SCHEMA_MIGRATIONS', true);
    }
    register_shutdown_function(function () {
        fwrite(STDOUT, json_encode($GLOBALS['queries']) . PHP_EOL);
    });
    $config = file_get_contents(__DIR__ . '/../config.php');
    $config = str_replace('new PDO(', 'new TestPDO(', $config);
    eval('?>' . $config);
    exit;
}

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

function runCase($case) {
    $process = proc_open([PHP_BINARY, __FILE__, '--child', $case],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), "Could not start $case");
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    return [$status, json_decode(trim($output), true), $errors];
}

[$status, $queries, $errors] = runCase('request');
check($status === 0 && count($queries) === 1 && str_starts_with($queries[0], 'SET NAMES'), "Request ran schema work: $errors");

[$status, $queries, $errors] = runCase('migrate');
check($status === 0 && is_array($queries), "Migration failed: $errors");
$sql = implode("\n", $queries);
foreach (['clans', 'clan_members', 'clan_boss', 'achievements_user', 'arena_challenges', 'site_settings', 'paypal_orders'] as $table) {
    check(str_contains($sql, "CREATE TABLE IF NOT EXISTS `$table`"), "Missing $table migration");
}
check(str_contains($sql, 'ADD COLUMN `daily_streak`'), 'Missing user column migration');
check(!str_contains($sql, 'ADD COLUMN `id_clan`') && !str_contains($sql, 'ADD COLUMN `title`'), 'Existing columns were added again');

[$status, $queries, $errors] = runCase('fail');
check($status !== 0 && str_contains($errors, 'Database migration failed: schema failure'), 'Migration failure was not reported');

echo "Database migration tests passed\n";
