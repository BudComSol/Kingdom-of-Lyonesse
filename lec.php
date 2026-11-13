<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$lec = fch("SELECT * FROM `lec_user` WHERE `id_user` = ? LIMIT 1", array($uid));
if (!$lec) {
    qry("INSERT INTO `lec_user` SET `id_user` = ?, `level` = '1'", array($uid));
    $lec = array('level' => 1);
}

$lec_fight = fch("SELECT * FROM `lec_fight` WHERE `id_user` = ? LIMIT 1", array($uid));

// Zone navigation
$level = number($_GET['level'] ?? 0);
if ($level == 1 && ($lec['level'] ?? 1) >= 2) {
    qry("UPDATE `lec_user` SET `level` = `level` - 1 WHERE `id_user` = ? LIMIT 1", array($uid));
    header('Location: /lec.php');
    exit();
}

if ($level == 2 && ($lec['level'] ?? 1) <= 3) {
    qry("UPDATE `lec_user` SET `level` = `level` + 1 WHERE `id_user` = ? LIMIT 1", array($uid));
    header('Location: /lec.php');
    exit();
}

// Attack boss action
$attack = abs(intval($_GET['attack'] ?? 0));
if ($attack >= 1 && ($user['lec'] ?? 0) >= 1) {
    $check = fch("SELECT * FROM `lec_boss` WHERE `id` = ? LIMIT 1", array($attack));
    if (!$check) {
        header('Location: /lec.php');
        exit();
    }

    $combatStats = effectiveCombatStats($user);
    qry("UPDATE `users` SET `lec` = `lec` - 1 WHERE `id` = ? LIMIT 1", array($uid));
    qry("UPDATE `skills` SET `time` = '0', `reset` = '0' WHERE `id_user` = ?", array($uid));

    qry("INSERT INTO `lec_fight` SET
        `id_user` = ?,
        `name` = ?,
        `id_bot` = ?,
        `bot_power` = ?,
        `bot_block` = ?,
        `bot_hp` = ?,
        `power` = ?,
        `block` = ?,
        `hp` = ?,
        `default` = ?,
        `default2` = ?,
        `type` = ?",
        array(
            $uid,
            $check['name'],
            $check['id'],
            $check['param'],
            $check['param'],
            $check['param'],
            $combatStats['power'],
            $combatStats['block'],
            $combatStats['health'],
            $check['param'],
            $combatStats['power'],
            $check['type']
        )
    );

    header('Location: /lec.php');
    exit();
}

// Purchase forest energy
$energy = abs(intval($_GET['coll'] ?? 0));
if ($energy == 2 && ($user['lec'] ?? 0) <= 0 && ($user['gold'] ?? 0) >= 20) {
    qry("UPDATE `users` SET `lec` = 100, `gold` = `gold` - 20 WHERE `id` = ? LIMIT 1", array($uid));
    msg("Forest Energy replenished (+100 Energy)!");
    header('Location: /lec.php');
    exit();
}

// Ongoing combat view
if ($lec_fight) {
    head("Combat - The Dark Forest", $uid);
?>
    <meta http-equiv="Refresh" content="6; URL=lec.php?attack_get">
<?php
    require_once __DIR__ . "/templates/fight_lec.php";
    foot($uid);
    exit();
}

head("The Dark Forest", $uid);

if ($energy == 1 && ($user['lec'] ?? 0) <= 0) {
?>
    <div class="tophead2 cntr">
        Do you want to purchase 100 forest energy charges?<br>
        <font color="#888">Cost: <img src="/images/ruby.png" width="17" height="17" alt="Ruby"> 20 Rubies</font><br><br>
        <a class="button" href="?coll=2">Yes, purchase (+100)</a> &nbsp; <a class="button button_danger" href="/lec.php">Cancel</a>
    </div>
<?php
}

$zoneNames = array(
    1 => 'Dark Forest Outskirts (Tier 1)',
    2 => 'Deep Dark Forest (Tier 2)',
    3 => "Dragon's Lair (Tier 3)",
    4 => 'Sanctuary of Holy Knights (Tier 4)'
);
$currentZone = (int)($lec['level'] ?? 1);
?>

<div class="tophead">
    <div class="ohh"><img class="img4" src="/images/lec/nps.png" width="50px" height="50px" alt="Guard"></div>
    <span class="lime">City Gate Guard</span><br>
    <small>Venture forth with caution! Only seasoned warriors survive the terrifying creatures lurking in the Dark Forest.</small>
</div>

<div class="tophead2 cntr">
    <a class="button" style="padding: 4px 10px; font-size: 0.8rem;" href="?level=1">◀ Previous Zone</a> &nbsp;
    <span class="orange"><b><?php echo $zoneNames[$currentZone] ?? 'Dark Forest'; ?></b></span>
    &nbsp; <a class="button" style="padding: 4px 10px; font-size: 0.8rem;" href="?level=2">Next Zone ▶</a>
</div>

<?php
$user_q = $BaseConnect->prepare("SELECT * FROM `lec_boss` WHERE `level` = ? ORDER BY `id` ASC LIMIT 5");
$user_q->execute([$currentZone]);

while ($boss = $user_q->fetch(PDO::FETCH_ASSOC)) {
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgItem" src="/images/lec/<?php echo $boss['id']; ?>.png" width="50" height="50" alt="Monster">
        </div>
        <span class="orange"><b><?php echo htmlspecialchars($boss['name']); ?></b></span><br>
        <small>
            <img src="/images/power.png" width="15px" height="15px"> Power: <?php echo $boss['param']; ?> &nbsp;
            <img src="/images/block.png" width="15px" height="15px"> Defense: <?php echo $boss['param']; ?> &nbsp;
            <img src="/images/health.png" width="15px" height="15px"> Health: <?php echo $boss['param']; ?>
            <br>
            Material drop: <img src="/images/<?php echo $boss['type']; ?>.png" width="16" height="16" alt="<?php echo $boss['type']; ?>"> <?php echo ucfirst($boss['type']); ?>
        </small>
    </div>
    <div class="tophead2 cntr">
        <div class="hrs"></div>
        <?php if (($user['lec'] ?? 0) >= 1) { ?>
            <a class="button button_red" href="?attack=<?php echo $boss['id']; ?>">⚔ Attack Monster</a>
        <?php } else { ?>
            <a class="button button_amber" href="?coll=1">Refill Energy</a>
        <?php } ?>
        <div class="hrs"></div>
    </div>
<?php
}
?>

<div class="tophead2 cntr">
    <small>
        Forest Energy: <b><?php echo $user['lec'] ?? 0; ?> / 100</b>
        <?php if (($user['lec'] ?? 0) <= 0) { ?>
            <a href="?coll=1"><span class="orange">(Refill Now)</span></a>
        <?php } ?>
        <br>
        <font color="#888">Defeat monsters in the Dark Forest to gather rare materials for equipment crafting and runes.</font>
    </small>
</div>

<?php
foot($uid);
?>