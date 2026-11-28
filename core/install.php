<?php

function installationConfigured($root) {
    if (file_exists($root . '/.env')) {
        return true;
    }
    foreach (['DB_NAME', 'DB_USER'] as $key) {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? '';
        }
        if (!is_string($value) || $value === '') {
            return false;
        }
    }
    return true;
}

function installerRequirements($root) {
    return [
        'PHP 8.0 or newer' => PHP_VERSION_ID >= 80000,
        'MySQL / MariaDB driver (pdo_mysql)' => extension_loaded('pdo_mysql'),
        'Multibyte text (mbstring)' => extension_loaded('mbstring'),
        'JSON, sessions and filter' => extension_loaded('json') && extension_loaded('session') && extension_loaded('filter'),
        'OpenSSL (secret encryption)' => extension_loaded('openssl'),
        'Writable configuration directory' => is_writable($root),
        'Readable database schema and seed data' => is_readable($root . '/_sql/database.sql'),
    ];
}

function installerSettings($input) {
    $values = [];
    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'APP_KEY'] as $key) {
        $value = $input[$key] ?? '';
        if (!is_string($value) || preg_match('/[\x00-\x1f\x7f]/', $value)) {
            throw new InvalidArgumentException('Settings must be text without control characters.');
        }
        $values[$key] = in_array($key, ['DB_PASS', 'APP_KEY'], true) ? $value : trim($value);
    }
    if (!preg_match('/^[a-zA-Z0-9._:-]+$/D', $values['DB_HOST'])) {
        throw new InvalidArgumentException('Enter a database hostname or IP address, without a protocol or socket path.');
    }
    if (!preg_match('/^[0-9]+$/D', $values['DB_PORT']) || (int)$values['DB_PORT'] < 1 || (int)$values['DB_PORT'] > 65535) {
        throw new InvalidArgumentException('The database port must be between 1 and 65535.');
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $values['DB_NAME']) || $values['DB_USER'] === '') {
        throw new InvalidArgumentException('Enter a database name (letters, numbers, underscores or hyphens) and database user.');
    }
    if ($values['APP_KEY'] === '') {
        $values['APP_KEY'] = bin2hex(random_bytes(32));
    } elseif (strlen($values['APP_KEY']) < 32 || trim($values['APP_KEY']) !== $values['APP_KEY']) {
        throw new InvalidArgumentException('Use a unique random secret key of at least 32 characters, without surrounding spaces.');
    }
    return $values;
}

// Respect SQL strings and comments; splitting on semicolons alone corrupts seed text.
function installerStatements($sql) {
    $statement = '';
    $quote = null;
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $sql[$i + 1] ?? '';
        if ($quote !== null) {
            $statement .= $char;
            if ($char === '\\' && $quote !== '`' && $next !== '') {
                $statement .= $next;
                $i++;
            } elseif ($char === $quote) {
                if ($next === $quote) {
                    $statement .= $next;
                    $i++;
                } else {
                    $quote = null;
                }
            }
        } elseif ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
            $statement .= $char;
        } elseif (($char === '-' && $next === '-' && preg_match('/\s/', $sql[$i + 2] ?? '')) || $char === '#') {
            while ($i < $length && $sql[$i] !== "\n") {
                $i++;
            }
            $statement .= "\n";
        } elseif ($char === '/' && $next === '*') {
            $end = strpos($sql, '*/', $i + 2);
            if ($end === false) {
                throw new RuntimeException('The database dump contains an unfinished comment.');
            }
            if (($sql[$i + 2] ?? '') === '!') {
                $statement .= substr($sql, $i, $end + 2 - $i);
            } else {
                $statement .= ' ';
            }
            $i = $end + 1;
        } elseif ($char === ';') {
            if (trim($statement) !== '') {
                yield trim($statement);
            }
            $statement = '';
        } else {
            $statement .= $char;
        }
    }
    if ($quote !== null) {
        throw new RuntimeException('The database dump contains an unfinished string.');
    }
    if (trim($statement) !== '') {
        yield trim($statement);
    }
}

function installerImport(PDO $database, $sql) {
    if ($database->query('SHOW TABLES')->fetchColumn() !== false) {
        throw new RuntimeException('This database is not empty. Choose a new empty database; existing game data will not be changed.');
    }
    foreach (installerStatements($sql) as $statement) {
        $database->exec($statement);
    }
    foreach (['users', 'items', 'lec_boss', 'news', 'quest', 'site_settings', 'paypal_orders'] as $table) {
        $database->query("SELECT 1 FROM `$table` LIMIT 1");
    }
    foreach (['items', 'lec_boss', 'news', 'quest'] as $table) {
        if ((int)$database->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() < 1) {
            throw new RuntimeException('Database import did not populate the required game data.');
        }
    }
}

function installerSaveConfiguration($root, $settings, $secure) {
    if (installationConfigured($root)) {
        throw new RuntimeException('The game is already configured. The installer will not replace its settings.');
    }
    $settings += [
        'DB_CHARSET' => 'utf8mb4',
        'APP_NAME' => 'Kingdom of Lyonesse',
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'BASE_URL' => 'auto',
        'SESSION_NAME' => 'LYONESSE_SESSID',
        'SESSION_LIFETIME' => '2592000',
        'SESSION_SECURE' => $secure ? 'true' : 'false',
        'SESSION_SAMESITE' => 'Lax',
    ];
    $content = "# Created by the Kingdom of Lyonesse installer. Keep this file private.\n";
    foreach ($settings as $key => $value) {
        $content .= $key . '="' . $value . "\"\n";
    }
    $temporary = tempnam($root, '.env.install-');
    if ($temporary === false) {
        throw new RuntimeException('Unable to create the configuration file. Check directory permissions.');
    }
    try {
        if (!chmod($temporary, 0600) || file_put_contents($temporary, $content) !== strlen($content)) {
            throw new RuntimeException('Unable to write a private configuration file.');
        }
        // Publish the complete file without ever overwriting an existing configuration.
        if (!link($temporary, $root . '/.env')) {
            throw new RuntimeException('Unable to publish .env. Check filesystem permissions and hard-link support.');
        }
    } finally {
        if (!unlink($temporary)) {
            error_log('Installer could not remove its temporary configuration file.');
        }
    }
}
