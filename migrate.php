<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run migrations from the command line.');
}

define('RUN_SCHEMA_MIGRATIONS', true);
require __DIR__ . '/config.php';

echo "Database migrations completed.\n";
