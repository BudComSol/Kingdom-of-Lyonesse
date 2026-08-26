<div class="tophead cntr">
    <font color="silver">Reach Level 30</font><br>
    <small>
        <?php if (($user['level'] ?? 1) <= 29) { ?>
            <font color="#888">Progress: <?php echo $user['level'] ?? 1; ?> of 30</font>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
$count_item = cnt("SELECT (id) FROM `inv` WHERE `yes` = '1' AND `set` >= '4' AND `id_user` = ?", array($uid));
?>
<div class="tophead cntr">
    <font color="silver">Equip 8 items (Legendary quality or higher)</font><br>
    <small>
        <?php if ($count_item <= 7) { ?>
            <font color="#888">Progress: <?php echo $count_item; ?> of 8</font><br>
            <a href="/shop.php?loc=1"><font color="orange"><u>Go to Merchant</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
$count_item2 = cnt("SELECT (id) FROM `inv` WHERE `yes` = '1' AND `rune` >= '2' AND `id_user` = ?", array($uid));
?>
<div class="tophead cntr">
    <font color="silver">Socket 8 Rare runes (or higher) into equipment</font><br>
    <small>
        <?php if ($count_item2 <= 7) { ?>
            <font color="#888">Progress: <?php echo $count_item2; ?> of 8</font><br>
            <a href="/shop.php?loc=3"><font color="orange"><u>Go to Runesmith</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
$count_skills = cnt("SELECT (id) FROM `skills` WHERE `level` >= '1' AND `id_user` = ?", array($uid));
?>
<div class="tophead cntr">
    <font color="silver">Train all 15 combat abilities</font><br>
    <small>
        <?php if ($count_skills <= 14) { ?>
            <font color="#888">Progress: <?php echo $count_skills; ?> of 15</font><br>
            <a href="/skill.php"><font color="orange"><u>Go to Abilities</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<div class="tophead cntr">
    <font color="silver">Fight in the Arena 15,000 times</font><br>
    <small>
        <?php if (($user['arena_tav'] ?? 0) <= 14999) { ?>
            <font color="#888">Progress: <?php echo $user['arena_tav'] ?? 0; ?> of 15,000</font><br>
            <a href="/arena.php"><font color="orange"><u>Go to Arena</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
if (isset($_GET['get']) && ($user['level'] ?? 1) >= 30 && $count_skills >= 15 && ($user['arena_tav'] ?? 0) >= 15000 && $count_item2 >= 8 && $count_item >= 8 && ($tavern['glava'] ?? 1) == 5) {
    $ruby_n = ($tavern['glava'] ?? 1) * 25;
    $gold_n = ($tavern['glava'] ?? 1) * 5000;
    $exp_n  = ($tavern['glava'] ?? 1) * 1000;

    qry("UPDATE `users` SET `gold` = `gold` + ?, `silver` = `silver` + ?, `exp` = `exp` + ? WHERE `id` = ? LIMIT 1", array($ruby_n, $gold_n, $exp_n, $uid));
    qry("UPDATE `tavern` SET `glava` = `glava` + 1 WHERE `id_user` = ? LIMIT 1", array($uid));
    msg("Chapter V Complete! All tavern quests mastered!");
    header('Location: /tavern.php');
    exit();
}

if (($user['level'] ?? 1) >= 30 && $count_skills >= 15 && ($user['arena_tav'] ?? 0) >= 15000 && $count_item2 >= 8 && $count_item >= 8 && ($tavern['glava'] ?? 1) == 5) {
?>
    <div class="tophead2 cntr">
        <font color="orange">You have completed all chapter quests!</font><br>
        <small>
            <font color="#888">
                Rewards:
                <img class="money" src="/images/ruby.png" width="16" height="16" alt="Rubies"> <?php echo ($tavern['glava'] ?? 1) * 25; ?> Rubies &nbsp;
                <img class="money" src="/images/gold.png" width="16" height="16" alt="Coins"> <?php echo ($tavern['glava'] ?? 1) * 5000; ?> Coins &nbsp;
                <img class="money" src="/images/exp.png" width="16" height="16" alt="Exp"> <?php echo ($tavern['glava'] ?? 1) * 1000; ?> Exp
            </font>
        </small>
        <div class="hrs"></div>
        <a class="button" href="?get">Claim Chapter Reward</a>
        <div class="hrs"></div>
    </div>
<?php
}
?>