<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

$sql = file_get_contents(__DIR__ . '/../database.sql');
check($sql !== false, 'Could not read database.sql');

preg_match_all('/^INSERT INTO `([^`]+)`/m', $sql, $matches);
$tables = $matches[1];
sort($tables);
check($tables === ['items', 'lec_boss', 'news', 'quest'], 'Unexpected seeded tables');
check(!in_array('users', $tables, true), 'Fresh install dump must not seed historical users');

echo "Database seed tests passed\n";
