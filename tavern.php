<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$tavern = fch("SELECT * FROM `tavern` WHERE `id_user` = ? LIMIT 1", array($uid));
if (!$tavern) {
    qry("INSERT INTO `tavern` SET `id_user` = ?, `level` = '1', `glava` = '1'", array($uid));
    $tavern = array('id_user' => $uid, 'level' => 1, 'id_quest' => 0, 'glava' => 1);
}

$go = number($_GET['go'] ?? 0);

if ($go == 1 && ($tavern['id_quest'] ?? 0) == 1) {
    qry("UPDATE `users` SET `metal` = `metal` + 10, `tkan` = `tkan` + 10 WHERE `id` = ? LIMIT 1", array($uid));
    qry("UPDATE `tavern` SET `level` = '2' WHERE `id_user` = ? LIMIT 1", array($uid));
    header('Location: /tavern.php');
    exit();
}

if ($go == 2 && ($tavern['id_quest'] ?? 0) == 2) {
    qry("UPDATE `users` SET `kamen` = `kamen` + 5 WHERE `id` = ? LIMIT 1", array($uid));
    qry("UPDATE `tavern` SET `level` = '3' WHERE `id_user` = ? LIMIT 1", array($uid));
    header('Location: /tavern.php');
    exit();
}

if ($go == 3 && ($tavern['id_quest'] ?? 0) == 3) {
    $combatStats = effectiveCombatStats($user);
    qry("INSERT INTO `tavern_fight` SET `id_user` = ?, `name` = 'Squire', `bot_power` = '35', `bot_block` = '35', `bot_hp` = '35', `power` = ?, `block` = ?, `hp` = ?",
        array($uid, $combatStats['power'], $combatStats['block'], $combatStats['health']));
    qry("UPDATE `tavern` SET `level` = '4' WHERE `id_user` = ? LIMIT 1", array($uid));
    header('Location: /tavern.php');
    exit();
}

head("Tavern & Story Quests", $uid);

if (($tavern['level'] ?? 1) != 4) {
?>
    <img style="top: 0; border-radius: 8px;" width="100%" src="/images/tavern/logo.png" alt="Tavern">
<?php
}

$tavernLevel = (int)($tavern['level'] ?? 1);
if ($tavernLevel >= 1 && $tavernLevel <= 5) {
    require_once __DIR__ . "/templates/tavern/tavern" . $tavernLevel . ".php";
}

foot($uid);
?>