<div class="tophead cntr">
    <font color="silver">Reach Level 5</font><br>
    <small>
        <?php if (($user['level'] ?? 1) <= 4) { ?>
            <font color="#888">Progress: <?php echo $user['level'] ?? 1; ?> of 5</font>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
$count_item = cnt("SELECT (id) FROM `inv` WHERE `yes` = '1' AND `smitch` >= '1' AND `id_user` = ?", array($uid));
?>
<div class="tophead cntr">
    <font color="silver">Forge / Upgrade any equipped item</font><br>
    <small>
        <?php if (!$count_item) { ?>
            <font color="#888">Progress: 0 of 1</font><br>
            <a href="/shop.php?loc=2"><font color="orange"><u>Go to Blacksmith</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
$count_skills = cnt("SELECT (id) FROM `skills` WHERE `level` >= '1' AND `id_user` = ?", array($uid));
?>
<div class="tophead cntr">
    <font color="silver">Train any combat ability</font><br>
    <small>
        <?php if (!$count_skills) { ?>
            <font color="#888">Progress: 0 of 1</font><br>
            <a href="/skill.php"><font color="orange"><u>Go to Abilities</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<div class="tophead cntr">
    <font color="silver">Learn Assassin Mastery</font><br>
    <small>
        <?php if (($user['kill'] ?? 0) <= 0) { ?>
            <font color="#888">Progress: 0 of 1</font><br>
            <a href="/shop.php?loc=4"><font color="orange"><u>Go to Master Trainer</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<div class="tophead cntr">
    <font color="silver">Learn Healer Mastery</font><br>
    <small>
        <?php if (($user['medik'] ?? 0) <= 0) { ?>
            <font color="#888">Progress: 0 of 1</font><br>
            <a href="/shop.php?loc=4"><font color="orange"><u>Go to Master Trainer</u></font></a>
        <?php } else { ?>
            <font color="lime">Quest Goal Achieved!</font>
        <?php } ?>
    </small>
</div>

<?php
if (isset($_GET['get']) && ($user['level'] ?? 1) >= 5 && ($user['kill'] ?? 0) >= 1 && ($user['medik'] ?? 0) >= 1 && $count_skills && $count_item && ($tavern['glava'] ?? 1) == 1) {
    $ruby_n = ($tavern['glava'] ?? 1) * 25;
    $gold_n = ($tavern['glava'] ?? 1) * 5000;
    $exp_n  = ($tavern['glava'] ?? 1) * 1000;

    qry("UPDATE `users` SET `gold` = `gold` + ?, `silver` = `silver` + ?, `exp` = `exp` + ? WHERE `id` = ? LIMIT 1", array($ruby_n, $gold_n, $exp_n, $uid));
    qry("UPDATE `tavern` SET `glava` = `glava` + 1 WHERE `id_user` = ? LIMIT 1", array($uid));
    msg("Chapter I Complete! Reward received!");
    header('Location: /tavern.php');
    exit();
}

if (($user['level'] ?? 1) >= 5 && ($user['kill'] ?? 0) >= 1 && ($user['medik'] ?? 0) >= 1 && $count_skills && $count_item) {
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