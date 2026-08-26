<?php
require_once __DIR__ . '/../config.php';

function qry($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fch($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Reset rare keys and chest
qry("UPDATE `users` SET `key2` = '0', `sun2` = '0'");

// Reward top 10 tournament leaders
$topLeaders = $BaseConnect->query("SELECT * FROM `users` WHERE `tour` >= '1' ORDER BY `tour` DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$leaderLines = array();
$rank = 1;
foreach ($topLeaders as $leader) {
    $rewardRubies = max(10, intval($leader['tour'] * 15 / 300));
    qry("UPDATE `users` SET `gold` = `gold` + ? WHERE `id` = ? LIMIT 1", array($rewardRubies, $leader['id']));
    $leaderLines[] = "{$rank}. [b]{$leader['login']}[/b] — {$leader['tour']} Trophies ([color=gold]+{$rewardRubies} Rubies[/color])";
    $rank++;
}

if (!empty($leaderLines)) {
    $announcementText = "The Royal Tournament has concluded! Congratulations to our champions:[br][br]" . implode("[br]", $leaderLines);
    $announcementText .= "[br][br]All participants who earned 250+ trophies have also been awarded a 50 Ruby participation prize!";

    qry("INSERT INTO `news` SET `time` = ?, `name` = ?, `text` = ?", array(time(), "Royal Tournament Results", $announcementText));
}

// Give 50 rubies to anyone with >= 250 trophies
qry("UPDATE `users` SET `gold` = `gold` + 50 WHERE `tour` >= '250'");

// Reset tournament points for all players
qry("UPDATE `users` SET `tour` = '0', `news` = '1'");
?>