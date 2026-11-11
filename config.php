<?php
/**
 * Application Configuration & Bootstrap
 * Kingdom of Lyonesse MMORPG Engine
 */

// ---------------------------------------------------------------------
// 1. Lightweight .env Parser & Loader
// ---------------------------------------------------------------------
if (!function_exists('loadEnv')) {
    function loadEnv($envPath) {
        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Strip outer quotes if present
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }

        if ($value === null) {
            return $default;
        }

        switch (strtolower((string)$value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

// Load .env from root directory
loadEnv(__DIR__ . '/.env');

// ---------------------------------------------------------------------
// 2. Application Constants & Auto Base URL
// ---------------------------------------------------------------------
if (!defined('BASE_URL')) {
    $envBaseUrl = env('BASE_URL');
    if (!empty($envBaseUrl) && strtolower($envBaseUrl) !== 'auto') {
        define('BASE_URL', rtrim($envBaseUrl, '/'));
    } else {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $scheme = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        define('BASE_URL', rtrim($scheme . $host, '/'));
    }
}
if (!defined('APP_NAME')) {
    define('APP_NAME', env('APP_NAME', 'Kingdom of Lyonesse'));
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', (bool)env('APP_DEBUG', false));
}

// ---------------------------------------------------------------------
// 3. Error Reporting
// ---------------------------------------------------------------------
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------------------
// 4. Secure Session Initialization (HttpOnly, SameSite, Secure)
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    $sessionLifetime = (int)env('SESSION_LIFETIME', 2592000); // 30 days default
    $isSecure = (bool)env('SESSION_SECURE', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
    $sameSite = env('SESSION_SAMESITE', 'Lax');
    $sessionName = env('SESSION_NAME', 'LYONESSE_SESSID');

    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => $sameSite
    ]);

    session_start();
}

if (!ob_get_level()) {
    ob_start();
}
mb_internal_encoding("UTF-8");

// ---------------------------------------------------------------------
// 5. Database Connection (PDO)
// ---------------------------------------------------------------------
$dbHost = env('DB_HOST', 'localhost');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_NAME', 'e99663xd_test');
$dbUser = env('DB_USER', 'e99663xd_test');
$dbPass = env('DB_PASS', 'e99663xd_test1');
$dbCharset = env('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";
    $pdoOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $BaseConnect = new PDO($dsn, $dbUser, $dbPass, $pdoOptions);
    $BaseConnect->exec("SET NAMES {$dbCharset}");

    // Auto-ensure modern gameplay schema (Clans, Achievements, Daily Streaks, Hero Titles)
    try {
        $BaseConnect->exec("
            CREATE TABLE IF NOT EXISTS `clans` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL,
              `leader_id` int(11) NOT NULL DEFAULT '0',
              `level` int(11) NOT NULL DEFAULT '1',
              `exp` int(11) NOT NULL DEFAULT '0',
              `gold` int(11) NOT NULL DEFAULT '0',
              `created_at` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `leader_id` (`leader_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `clan_members` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `clan_id` int(11) NOT NULL,
              `user_id` int(11) NOT NULL,
              `rank` int(11) NOT NULL DEFAULT '1',
              `joined_at` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `clan_id` (`clan_id`),
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `clan_boss` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `clan_id` int(11) NOT NULL,
              `boss_name` varchar(50) NOT NULL,
              `hp` int(11) NOT NULL,
              `max_hp` int(11) NOT NULL,
              `reward_gold` int(11) NOT NULL DEFAULT '1000',
              `reward_exp` int(11) NOT NULL DEFAULT '500',
              `last_attack` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `clan_id` (`clan_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `achievements_user` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `achievement_id` varchar(50) NOT NULL,
              `progress` int(11) NOT NULL DEFAULT '0',
              `unlocked` tinyint(1) NOT NULL DEFAULT '0',
              `unlocked_at` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              KEY `achievement_id` (`achievement_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        ");

        $userCols = $BaseConnect->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
        $newCols = [
            'id_clan'          => "ALTER TABLE `users` ADD COLUMN `id_clan` int(11) NOT NULL DEFAULT 0",
            'daily_streak'     => "ALTER TABLE `users` ADD COLUMN `daily_streak` int(11) NOT NULL DEFAULT 0",
            'daily_last_claim' => "ALTER TABLE `users` ADD COLUMN `daily_last_claim` int(11) NOT NULL DEFAULT 0",
            'title'            => "ALTER TABLE `users` ADD COLUMN `title` varchar(50) NOT NULL DEFAULT ''",
            'kills_boss'       => "ALTER TABLE `users` ADD COLUMN `kills_boss` int(11) NOT NULL DEFAULT 0",
            'kills_pvp'        => "ALTER TABLE `users` ADD COLUMN `kills_pvp` int(11) NOT NULL DEFAULT 0",
            'forged_items'     => "ALTER TABLE `users` ADD COLUMN `forged_items` int(11) NOT NULL DEFAULT 0"
        ];
        foreach ($newCols as $col => $alterSql) {
            if (!in_array($col, $userCols)) {
                $BaseConnect->exec($alterSql);
            }
        }
    } catch (Exception $e) {
        // Schema auto-migration fallback
    }

    try {
        $BaseConnect->exec("
            CREATE TABLE IF NOT EXISTS `arena_challenges` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `challenger_id` int(11) NOT NULL,
              `opponent_id` int(11) NOT NULL,
              `challenger_stats` text NOT NULL,
              `status` varchar(16) NOT NULL DEFAULT 'pending',
              `winner_id` int(11) NOT NULL DEFAULT 0,
              `battle_log` text NOT NULL,
              `created_at` int(11) NOT NULL DEFAULT 0,
              `resolved_at` int(11) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              KEY `opponent_status` (`opponent_id`, `status`),
              KEY `challenger_status` (`challenger_id`, `status`),
              KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (PDOException $e) {
        error_log('Arena challenge schema migration failed: ' . $e->getMessage());
        die('Arena challenge storage is unavailable. Please contact the game administrator.');
    }
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die('Database connection error: ' . htmlspecialchars($e->getMessage()));
    } else {
        die('Database connection error! Please check your configuration.');
    }
}
