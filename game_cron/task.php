<?php
require_once __DIR__ . '/../config.php';

function qry($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Reset daily completed quests whose cooldown has passed
qry("DELETE FROM `quest_user` WHERE `last` >= ?", array(time()));

// Reset daily keys and common chest
qry("UPDATE `users` SET `key` = '0', `sun1` = '0'");

// Reset daily forest resource caps
qry("UPDATE `lec_user` SET `metal` = '0', `tkan` = '0', `book` = '0', `kamen` = '0', `swit` = '0'");
?>