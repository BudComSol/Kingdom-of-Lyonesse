<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$type  = number($_GET['type'] ?? 1);
if ($type <= 0 || $type >= 5) {
    $type = 1;
}

$skill = number($_GET['skill'] ?? 0);
$color = number($_GET['color'] ?? 0);
$get   = number($_GET['get'] ?? 0);
$up    = number($_GET['up'] ?? 0);

// Clear ability notification if set
if (($user['skills_check'] ?? 0) != 0) {
    qry("UPDATE `users` SET `skills_check` = '0' WHERE `id` = ? LIMIT 1", array($uid));
}

// ---------------------------------------------------------------------
// 1. ACTION: UPGRADE ABILITY
// ---------------------------------------------------------------------
if ($up >= 1) {
    $info = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $up));
    if (!$info) {
        err("Ability not found!");
        header('Location: /skill.php?type=' . $type);
        exit();
    }

    $currentLevel = (int)($info['level'] ?? 0);
    if ($currentLevel >= 10) {
        err("This ability is already at maximum level (10 of 10)!");
        header('Location: /skill.php?type=' . $type);
        exit();
    }

    $requiredPoints = $currentLevel + 1;
    $availablePoints = (int)($user['skills'] ?? 0);

    if ($availablePoints < $requiredPoints) {
        err("Not enough Ability Points! You need {$requiredPoints} AP to upgrade this ability (You have: {$availablePoints} AP).");
        header('Location: /skill.php?type=' . $type);
        exit();
    }

    // Deduct points from user
    qry("UPDATE `users` SET `skills` = `skills` - ? WHERE `id` = ? LIMIT 1", array($requiredPoints, $uid));
    
    // Upgrade ability stats
    qry("UPDATE `skills` SET `param` = `param` + 1, `time_max` = GREATEST(1, `time_max` - 2), `level` = `level` + 1 WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $up));
    
    msg("Ability upgraded to Level " . ($currentLevel + 1) . " successfully!");
    header('Location: /skill.php?type=' . $type);
    exit();
}

// ---------------------------------------------------------------------
// 2. ACTION: EQUIP TO BATTLE DECK SLOT
// ---------------------------------------------------------------------
$info4 = ($skill >= 1) ? fch("SELECT * FROM `skills_fight` WHERE `id_user` = ? AND `id_skills` = ? LIMIT 1", array($uid, $skill)) : null;

if ($skill >= 1 && $color >= 1 && $get >= 1 && !$info4) {
    if ($get < 1 || $get > 4) {
        header('Location: /skill.php?type=' . $type);
        exit();
    }

    qry("UPDATE `skills_fight` SET `id_skills` = ? WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($skill, $uid, $get));
    msg("Ability equipped to Battle Deck slot {$get} successfully!");
    header('Location: /skill.php?type=' . $type);
    exit();
}

$name_skill = array(
    '',
    'Scarlet Blood',
    'Justice',
    'Precision Strike',
    'Rage',
    'Despair',
    'Healing',
    'Venomous Bite',
    'Piercing',
    'Sentence',
    'Blade Dance',
    'Power Surge',
    'Dead Blade',
    'Aura of Darkness',
    'Frozen Heart',
    'Demon Wings'
);

$color_skill = array('', '#ff8000', '#32cd32', '#a335ee');

// ---------------------------------------------------------------------
// 3. VIEW: EQUIP ABILITY TO SLOT
// ---------------------------------------------------------------------
if ($skill >= 1 && $color >= 1 && !$info4) {
    if ($color <= 0 || $color >= 4) {
        header('Location: /skill.php?type=' . $type);
        exit();
    }

    $skills_info = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $skill));
    if (!$skills_info || ($skills_info['level'] ?? 0) <= 0) {
        header('Location: /skill.php?type=' . $type);
        exit();
    }

    head("Equip Ability", $uid);

    $kill1  = intval(($user['kill'] ?? 0) * 5);
    $kill   = intval($skills_info['param'] * $kill1 / 100);
    $medik1 = intval(($user['medik'] ?? 0) * 5);
    $medik  = intval($skills_info['param'] * $medik1 / 100);
    $mag1   = intval(($user['mag'] ?? 0) * 5);
    $mag    = intval($skills_info['param'] * $mag1 / 100);

    $text_skill = array(
        '',
        'Drains <span class="lime">' . ($skills_info['param'] + $kill) . '%</span> enemy HP, ignoring defense<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Reduces enemy defense and power by <span class="lime">' . ($skills_info['param'] + $kill) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Deals <span class="lime">' . ($skills_info['param'] + $kill) . '%</span> direct damage, ignoring defense<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Increases your power during combat by <span class="lime">' . ($skills_info['param'] + $kill) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Increases your defense during combat by <span class="lime">' . ($skills_info['param'] + $kill) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Restores <span class="lime">' . ($skills_info['param'] + $medik) . '%</span> of your health<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Steals <span class="lime">' . ($skills_info['param'] + $medik) . '%</span> health from the enemy<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Enemy cannot attack you<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Enemy attacks themselves with <span class="lime">' . ($skills_info['param'] + $medik) . '%</span> power<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Increases your attack speed by <span class="lime">' . ($skills_info['param'] + $medik) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Steals <span class="lime">' . ($skills_info['param'] + $mag) . '%</span> of enemy power<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Decreases enemy attack speed by <span class="lime">' . ($skills_info['param'] + $mag) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Enemy loses you and attacks your illusion<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Absorbs all incoming damage<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Enemy cannot use abilities<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>'
    );
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgSkill" src="/images/skills/<?php echo $skills_info['type']; ?>.png" width="50" height="50" alt="Ability">
        </div>
        <font color="<?php echo $color_skill[$color] ?? '#fff'; ?>">
            <b><?php echo $name_skill[$skills_info['type']] ?? 'Ability'; ?></b>
        </font>
        <br>
        <small>
            Level: <?php echo $skills_info['level']; ?> of 10<br>
            <?php echo $text_skill[$skills_info['type']] ?? ''; ?>
        </small>
    </div>

    <div class="tophead2 cntr"><span class="color"><b>Select Battle Deck Slot to Equip:</b></span></div>
<?php
    for ($i = 1; $i < 5; $i++) {
        $fight = fch("SELECT * FROM `skills_fight` WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($uid, $i));
        $sk = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $fight['id_skills'] ?? 0));

        $c = 1;
        if (($sk['type'] ?? 0) >= 6 && ($sk['type'] ?? 0) <= 10) $c = 2;
        if (($sk['type'] ?? 0) >= 11 && ($sk['type'] ?? 0) <= 15) $c = 3;
?>
        <div class="tophead">
        <?php if (!$fight || ($fight['id_skills'] ?? 0) == 0 || !$sk) { ?>
            <div class="ohh"><div class="items-icons"></div></div>
            <b>Slot <?php echo $i; ?> (Empty)</b><br>
            <small><font color="#777">Equip this ability into empty slot <?php echo $i; ?></font></small>
            <br><br>
            <div class="cntr">
                <a class="button" href="?type=<?php echo $type; ?>&skill=<?php echo $skill; ?>&color=<?php echo $color; ?>&get=<?php echo $i; ?>">Equip to Slot <?php echo $i; ?></a>
            </div>
        <?php } else { ?>
            <div class="ohh">
                <img class="imgSkill" src="/images/skills/<?php echo $sk['type']; ?>.png" width="45" height="45" alt="">
            </div>
            <b>Slot <?php echo $i; ?>: <font color="<?php echo $color_skill[$c] ?? '#fff'; ?>"><?php echo $name_skill[$sk['type']] ?? 'Ability'; ?></font></b> (Lvl <?php echo $sk['level']; ?>)
            <br>
            <small><font color="#999">Click below to replace currently equipped ability</font></small>
            <br><br>
            <div class="cntr">
                <a class="button button_gold" href="?type=<?php echo $type; ?>&skill=<?php echo $skill; ?>&color=<?php echo $color; ?>&get=<?php echo $i; ?>">Replace in Slot <?php echo $i; ?></a>
            </div>
        <?php } ?>
        </div>
<?php
    }
?>
    <a href="?type=<?php echo $type; ?>" class="tophead cntr">← Back to Abilities</a>
<?php
    foot($uid);
    exit();
}

// ---------------------------------------------------------------------
// 4. MAIN VIEW: ABILITY LISTING & UPGRADES
// ---------------------------------------------------------------------
head("Abilities", $uid);

if ($type == 1) {
    $name = 'Assassin Abilities';
    $link = 2;
    $coll = 1;
    $coll2 = 5;
} elseif ($type == 2) {
    $name = 'Healer Abilities';
    $link = 3;
    $coll = 6;
    $coll2 = 10;
} elseif ($type == 3) {
    $name = 'Warlock Abilities';
    $link = 4;
    $coll = 11;
    $coll2 = 15;
} else {
    $name = 'All Abilities';
    $link = 1;
    $coll = 1;
    $coll2 = 15;
}
?>

<div class="tophead cntr">
    <span class="color"><b>Active Battle Deck</b></span>
    <div class="hrs"></div>
    <?php
    for ($i = 1; $i < 5; $i++) {
        $fight = fch("SELECT * FROM `skills_fight` WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($uid, $i));
        $inf = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $fight['id_skills'] ?? 0));

        if (!$fight || ($fight['id_skills'] ?? 0) == 0 || !$inf) {
            echo '<div class="items-icons" title="Empty Slot ' . $i . '"></div>';
        } else {
            echo '<img class="imgSkill" src="/images/skills/' . $inf['type'] . '.png" width="46" height="46" alt="Skill ' . $i . '" title="' . htmlspecialchars($name_skill[$inf['type']] ?? '') . ' (Slot ' . $i . ')">';
        }
    }
    ?>
    <div class="hrs"></div>
</div>

<div class="tophead2">
    Filter: <a href="?type=<?php echo $link; ?>"><u><b><?php echo $name; ?></b></u> (Click to cycle)</a>
    <span style="float: right;">
        <img src="/images/skills.png" width="16" height="16" alt="Ability Points"> AP: <span class="orange"><b><?php echo $user['skills'] ?? 0; ?></b></span>
    </span>
</div>

<?php
for ($i = $coll; $i <= $coll2; $i++) {
    $skills_info = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($uid, $i));
    if (!$skills_info) continue;

    $kill1  = intval(($user['kill'] ?? 0) * 5);
    $kill   = intval($skills_info['param'] * $kill1 / 100);
    $medik1 = intval(($user['medik'] ?? 0) * 5);
    $medik  = intval($skills_info['param'] * $medik1 / 100);
    $mag1   = intval(($user['mag'] ?? 0) * 5);
    $mag    = intval($skills_info['param'] * $mag1 / 100);

    $text_skill = array(
        '',
        'Drains <span class="lime">' . ($skills_info['param'] + $kill) . '%</span> enemy HP, ignoring defense<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Reduces enemy defense and power by <span class="lime">' . ($skills_info['param'] + $kill) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Deals <span class="lime">' . ($skills_info['param'] + $kill) . '%</span> direct damage, ignoring defense<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Increases your power during combat by <span class="lime">' . ($skills_info['param'] + $kill) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Increases your defense during combat by <span class="lime">' . ($skills_info['param'] + $kill) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Restores <span class="lime">' . ($skills_info['param'] + $medik) . '%</span> of your health<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Steals <span class="lime">' . ($skills_info['param'] + $medik) . '%</span> health from the enemy<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Enemy cannot attack you<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Enemy attacks themselves with <span class="lime">' . ($skills_info['param'] + $medik) . '%</span> power<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Increases your attack speed by <span class="lime">' . ($skills_info['param'] + $medik) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Steals <span class="lime">' . ($skills_info['param'] + $mag) . '%</span> of enemy power<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Decreases enemy attack speed by <span class="lime">' . ($skills_info['param'] + $mag) . '%</span><br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Enemy loses you and attacks your illusion<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span>',
        'Absorbs all incoming damage<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>',
        'Enemy cannot use abilities<br>Cooldown: <span class="yellow">' . $skills_info['time_max'] . 's</span><br>Duration: <span class="yellow">' . $skills_info['reset_max'] . 's</span>'
    );

    $c = 1;
    if ($i >= 6 && $i <= 10) $c = 2;
    if ($i >= 11 && $i <= 15) $c = 3;

    $info2 = fch("SELECT * FROM `skills_fight` WHERE `id_user` = ? AND `id_skills` = ? LIMIT 1", array($uid, $skills_info['id']));
    $reqPoints = ($skills_info['level'] ?? 0) + 1;
    $hasEnoughPoints = (($user['skills'] ?? 0) >= $reqPoints);
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgSkill" src="/images/skills/<?php echo $i; ?>.png" width="50" height="50" alt="Skill">
        </div>
        <font color="<?php echo $color_skill[$c] ?? '#fff'; ?>">
            <b><?php echo $name_skill[$i] ?? 'Ability'; ?></b>
        </font>
        <small>
            (Level: <b><?php echo $skills_info['level']; ?> of 10</b>)
            <?php if (($skills_info['level'] ?? 0) >= 1 && !$info2) { ?>
                <span style="float: right;">
                    <a href="?type=<?php echo $type; ?>&skill=<?php echo $skills_info['id']; ?>&color=<?php echo $c; ?>" title="Equip to Battle Deck">
                        <img src="/images/icon_reply.png" width="18" height="18" alt="Equip">
                    </a>
                </span>
            <?php } ?>
            <br>
            <?php echo $text_skill[$i] ?? ''; ?>
        </small>
    </div>

    <?php if (($skills_info['level'] ?? 0) <= 9) { ?>
        <div class="tophead2 cntr">
            <div class="hrs"></div>
            <a class="button <?php echo $hasEnoughPoints ? '' : 'button_danger'; ?>" href="?type=<?php echo $type; ?>&up=<?php echo $skills_info['id']; ?>">
                Upgrade <img src="/images/skills.png" width="15" height="15" alt="AP"> <?php echo $reqPoints; ?> AP
            </a>
            <div class="hrs"></div>
        </div>
    <?php } else { ?>
        <div class="tophead2 cntr">
            <span class="yellow"><b>★ Maximum Level Achieved (10 of 10) ★</b></span>
        </div>
    <?php } ?>
<?php
}

foot($uid);
?>