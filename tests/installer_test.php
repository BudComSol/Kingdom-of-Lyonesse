<?php

require __DIR__ . '/../core/install.php';

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

function rejects($callback, $message) {
    try {
        $callback();
    } catch (InvalidArgumentException $exception) {
        return;
    }
    throw new RuntimeException($message);
}

$input = ['DB_HOST' => 'localhost', 'DB_PORT' => '3306', 'DB_NAME' => 'lyonesse_test', 'DB_USER' => 'test', 'DB_PASS' => ''];
$settings = installerSettings($input);
check(strlen($settings['APP_KEY']) === 64 && ctype_xdigit($settings['APP_KEY']), 'Blank secret must generate a secure key');
check($settings['APP_KEY'] !== installerSettings($input)['APP_KEY'], 'Generated keys must be unique');
check(installerSettings($input + ['APP_KEY' => str_repeat('k', 32)])['APP_KEY'] === str_repeat('k', 32), 'Entered secret was replaced');
foreach ([
    ['DB_HOST' => 'localhost;dbname=other'],
    ['DB_NAME' => 'test;other'],
    ['DB_PORT' => '0'],
    ['DB_PORT' => '65536'],
    ['DB_PORT' => '3306suffix'],
    ['DB_USER' => ''],
    ['DB_PASS' => "password\nAPP_DEBUG=true"],
    ['DB_PASS' => ['not text']],
    ['APP_KEY' => 'too-short'],
    ['APP_KEY' => ' ' . str_repeat('k', 32)],
] as $invalid) {
    rejects(fn() => installerSettings(array_replace($input, $invalid)), 'Invalid setting was accepted');
}

$statements = iterator_to_array(installerStatements(
    "-- ignore ;\nCREATE TABLE `example` (`text` TEXT); /* ignore ; */\n"
    . "INSERT INTO `example` VALUES ('a; b', 'it''s fine', 'escaped\\';value');\n"
    . "# ignore ;\n/*!40101 SET NAMES utf8mb4 */;"
));
check(count($statements) === 3, 'SQL strings or comments were split incorrectly');
check(str_contains($statements[1], "'a; b'") && str_contains($statements[1], "'escaped\\';value'"), 'Seed strings were changed');
check($statements[2] === '/*!40101 SET NAMES utf8mb4 */', 'Executable MySQL comment was discarded');
$dump = file_get_contents(__DIR__ . '/../_sql/database.sql');
$dumpStatements = iterator_to_array(installerStatements($dump));
check(count(array_filter($dumpStatements, fn($sql) => str_starts_with($sql, 'INSERT INTO'))) === 4, 'Seed inserts were lost');
check(count(array_filter($dumpStatements, fn($sql) => str_starts_with($sql, 'CREATE TABLE'))) === 29, 'Schema tables were lost');

$originalEnvironment = [];
foreach (['DB_NAME', 'DB_USER'] as $key) {
    $originalEnvironment[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
    putenv($key);
    unset($_ENV[$key], $_SERVER[$key]);
}
$directory = sys_get_temp_dir() . '/lyonesse-installer-' . bin2hex(random_bytes(8));
check(mkdir($directory, 0700), 'Cannot create isolated configuration test directory');
try {
    check(!installationConfigured($directory), 'Fresh install was marked configured');
    putenv('DB_NAME=external_game');
    putenv('DB_USER=external_user');
    check(installationConfigured($directory), 'Server-managed installation was not detected');
    putenv('DB_NAME');
    putenv('DB_USER');
    $settings['DB_PASS'] = 'true';
    installerSaveConfiguration($directory, $settings, false);
    check(installationConfigured($directory), 'Saved configuration did not lock installation');
    check((fileperms($directory . '/.env') & 0777) === 0600, 'Configuration permissions are not private');
    $config = file_get_contents(__DIR__ . '/../config.php');
    $start = strpos($config, "if (!function_exists('loadEnv'))");
    $end = strpos($config, '// Load .env from root directory');
    eval(substr($config, $start, $end - $start));
    loadEnv($directory . '/.env');
    check(env('DB_PASS', '', false) === 'true', 'Literal database password was coerced');
    check(env('APP_KEY') === $settings['APP_KEY'], 'Secret key did not round-trip through .env');
    check(env('SESSION_SECURE') === false && env('APP_DEBUG') === false, 'HTTP or production configuration was incorrect');
    $before = file_get_contents($directory . '/.env');
    try {
        installerSaveConfiguration($directory, installerSettings($input), true);
        throw new LogicException('Configured installation was overwritten');
    } catch (RuntimeException $exception) {
        check(file_get_contents($directory . '/.env') === $before, 'Configuration changed on reinstall');
    }
} finally {
    foreach (array_keys($settings + ['DB_CHARSET' => '', 'APP_NAME' => '', 'APP_ENV' => '', 'APP_DEBUG' => '', 'BASE_URL' => '', 'SESSION_NAME' => '', 'SESSION_LIFETIME' => '', 'SESSION_SECURE' => '', 'SESSION_SAMESITE' => '']) as $key) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
    foreach ($originalEnvironment as $key => [$environment, $env, $server]) {
        if ($environment !== false) putenv("$key=$environment");
        if ($env !== null) $_ENV[$key] = $env;
        if ($server !== null) $_SERVER[$key] = $server;
    }
    if (file_exists($directory . '/.env')) unlink($directory . '/.env');
    rmdir($directory);
}

if (getenv('INSTALL_TEST_DSN')) {
    $database = new PDO(getenv('INSTALL_TEST_DSN'), getenv('INSTALL_TEST_USER'), getenv('INSTALL_TEST_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    installerImport($database, $dump);
    check((int)$database->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0, 'Fresh install seeded players');
    check((int)$database->query("SHOW TABLE STATUS LIKE 'users'")->fetch(PDO::FETCH_ASSOC)['Auto_increment'] === 1, 'Player IDs do not start at 1');
    $itemCount = (int)$database->query('SELECT COUNT(*) FROM items')->fetchColumn();
    try {
        installerImport($database, $dump);
        throw new LogicException('Nonempty database was accepted');
    } catch (RuntimeException $exception) {
        check(str_contains($exception->getMessage(), 'not empty'), 'Reinstall failure did not explain nonempty database');
    }
    check((int)$database->query('SELECT COUNT(*) FROM items')->fetchColumn() === $itemCount, 'Existing data changed during reinstall');
    echo "Installer database integration tests passed\n";
}

echo "Installer tests passed\n";
