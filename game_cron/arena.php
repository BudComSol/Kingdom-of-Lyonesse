<?php
require_once __DIR__ . '/../config.php';

function qry($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Replenish arena & forest battles
qry("UPDATE `users` SET `arena` = '20'");
qry("UPDATE `users` SET `lec` = '100'");
?>