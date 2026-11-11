<?php
$tavf = fch("SELECT * FROM `tavern_fight` WHERE `id_user` = ? LIMIT 1", array($uid));
$combatStats = effectiveCombatStats($user);

if (!$tavf) {
    header('Location: /tavern.php');
    exit();
}

$power_opp = max(1, rand(round($tavf['bot_power'] / 4), round($tavf['bot_power'] / 3)));
$block_opp = max(1, rand(round($tavf['bot_block'] / 10), round($tavf['bot_block'] / 8)));
$power     = max(1, rand(round($tavf['power'] / 4), round($tavf['power'] / 3)));
$block     = max(1, rand(round($tavf['block'] / 10), round($tavf['block'] / 8)));

$power_opp = max(0, $power_opp - $block);
$power     = max(0, $power - $block_opp);
$isCrit = rand(1, 100) <= 15 + (int)$combatStats['critical_chance'];
if ($isCrit) {
    $power = (int)($power * 1.5);
}

if (isset($_GET['attack'])) {
    if ($power >= $tavf['bot_hp']) {
        $questExp = clanRewardAmount($uid, 30);
        qry("UPDATE `users` SET `exp` = `exp` + ? WHERE `id` = ? LIMIT 1", array($questExp, $uid));
        qry("UPDATE `tavern` SET `id_quest` = '4', `level` = '5' WHERE `id_user` = ? LIMIT 1", array($uid));
        qry("DELETE FROM `tavern_fight` WHERE `id_user` = ?", array($uid));
        msg2("You defeated the Squire!");
        header('Location: /tavern.php');
        exit();
    }

    qry("UPDATE `tavern_fight` SET `hp` = `hp` - ?, `bot_hp` = `bot_hp` - ? WHERE `id` = ? LIMIT 1", array($power_opp, $power, $tavf['id']));
    header('Location: /tavern.php');
    exit();
}

$botHpPercent = round(max(0, $tavf['bot_hp']) / max(1, $tavf['bot_power']) * 100, 1);
$heroHpPercent = round(max(0, $tavf['hp']) / max(1, $combatStats['health']) * 100, 1);
?>

<div class="tophead">
    <div class="ohh">
        <img class="imgSkill" src="/images/tavern/nps2.png" width="44" height="44" alt="Squire">
    </div>
    <span class="orange">Tavern Squire</span><br>
    <img src="/images/power.png" width="15px" height="15px"> <?php echo $tavf['bot_power']; ?>
    <img src="/images/block.png" width="15px" height="15px"> <?php echo $tavf['bot_block']; ?>
    <img src="/images/health.png" width="15px" height="15px"> <?php echo max(0, $tavf['bot_hp']); ?>

    <div class="hrs"></div>
    <div class="progress-bar exp">
        <div style="width:<?php echo $botHpPercent; ?>%;" class="progress _red"></div>
    </div>
</div>

<div class="list2 cntr">
    <div class="hrs"></div>
    <a href="?attack">
        <img class="imgSkill" src="/images/attack.png" width="40" height="40" alt="Strike">
    </a>
    <div class="hrs"></div>
</div>

<div class="tophead">
    <div class="ohh">
        <img class="imgSkill" src="/images/avatar/<?php echo $user['sex'] ?? 1; ?>/<?php echo $user['avatar'] ?? 1; ?>.png" width="40" height="40" alt="Hero">
    </div>
    <span class="orange"><?php echo htmlspecialchars($user['login'] ?? 'Knight'); ?></span><br>
    <img src="/images/power.png" width="15px" height="15px"> <?php echo $tavf['power']; ?>
    <img src="/images/block.png" width="15px" height="15px"> <?php echo $tavf['block']; ?>
    <img src="/images/health.png" width="15px" height="15px"> <?php echo max(0, $tavf['hp']); ?>

    <div class="hrs"></div>
    <div class="progress-bar exp">
        <div style="width:<?php echo $heroHpPercent; ?>%;" class="progress _green"></div>
    </div>
</div>