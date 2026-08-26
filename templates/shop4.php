<?php
$level = array(
    '0' => 5, '1' => 10, '2' => 20, '3' => 40, '4' => 60,
    '5' => 80, '6' => 100, '7' => 150, '8' => 300, '9' => 500, '10' => 1000
);

if (($user['premium_time'] ?? 0) > time()) {
    $killCost  = $level[$user['kill'] ?? 0] ?? 5;
    $kill      = $killCost - intval($killCost * 15 / 100);
    $medikCost = $level[$user['medik'] ?? 0] ?? 5;
    $medik     = $medikCost - intval($medikCost * 15 / 100);
    $magCost   = $level[$user['mag'] ?? 0] ?? 5;
    $mag       = $magCost - intval($magCost * 15 / 100);
} else {
    $kill  = $level[$user['kill'] ?? 0] ?? 5;
    $medik = $level[$user['medik'] ?? 0] ?? 5;
    $mag   = $level[$user['mag'] ?? 0] ?? 5;
}

$type = number($_GET['type'] ?? 0);

if ($type == 1 && ($user['kill'] ?? 0) <= 9) {
    if (($user['book'] ?? 0) < $kill) {
        err("Not enough skill books!");
        header('Location: ?loc=4');
        exit();
    }
    qry("UPDATE `users` SET `book` = `book` - ?, `kill` = `kill` + 1 WHERE `id` = ? LIMIT 1", array($kill, $uid));
    msg("Assassin mastery increased to " . (($user['kill'] ?? 0) + 1) . "/10!");
    header('Location: ?loc=4');
    exit();
}

if ($type == 2 && ($user['medik'] ?? 0) <= 9) {
    if (($user['book'] ?? 0) < $medik) {
        err("Not enough skill books!");
        header('Location: ?loc=4');
        exit();
    }
    qry("UPDATE `users` SET `book` = `book` - ?, `medik` = `medik` + 1 WHERE `id` = ? LIMIT 1", array($medik, $uid));
    msg("Healer mastery increased to " . (($user['medik'] ?? 0) + 1) . "/10!");
    header('Location: ?loc=4');
    exit();
}

if ($type == 3 && ($user['mag'] ?? 0) <= 9) {
    if (($user['book'] ?? 0) < $mag) {
        err("Not enough skill books!");
        header('Location: ?loc=4');
        exit();
    }
    qry("UPDATE `users` SET `book` = `book` - ?, `mag` = `mag` + 1 WHERE `id` = ? LIMIT 1", array($mag, $uid));
    msg("Warlock mastery increased to " . (($user['mag'] ?? 0) + 1) . "/10!");
    header('Location: ?loc=4');
    exit();
}

head("Richard - Master Trainer", $uid);
?>

<div class="tophead">
    <div class="ohh"><img class="img4" src="/images/shop4.png" width="50px" height="50px" alt="Richard"></div>
    <span class="lime">Richard (Master Trainer)</span><br>
    <small>Brave knight! Masteries unlock your true combat power. Study ancient skill books to enhance your abilities!</small>
</div>
<br>

<div class="tophead2 cntr">
    Your Skill Books: <img src="/images/book.png" width="16" height="16" alt="Books"> <b><?php echo $user['book'] ?? 0; ?></b>
</div>

<div class="tophead">
    <div class="ohh"><img class="img1" src="/images/t1.png" width="50" height="50" alt="Assassin"></div>
    <span class="orange"><b>Assassin Mastery</b></span>
    <small>
        (<?php echo $user['kill'] ?? 0; ?> of 10)<br>
        Increases Assassin ability effects by <span class="lime"><b><?php echo ($user['kill'] ?? 0) * 5; ?>%</b></span>
    </small>
</div>

<?php if (($user['kill'] ?? 0) <= 9) { ?>
<div class="tophead2 cntr">
    <div class="hrs"></div>
    <a class="button" href="?loc=4&type=1">
        Train Mastery <img src="/images/book.png" width="17" height="17" alt="Books"> <?php echo $kill; ?>
    </a>
    <div class="hrs"></div>
</div>
<?php } ?>

<div class="tophead">
    <div class="ohh"><img class="img1" src="/images/t2.png" width="50" height="50" alt="Healer"></div>
    <span class="orange"><b>Healer Mastery</b></span>
    <small>
        (<?php echo $user['medik'] ?? 0; ?> of 10)<br>
        Increases Healer ability effects by <span class="lime"><b><?php echo ($user['medik'] ?? 0) * 5; ?>%</b></span>
    </small>
</div>

<?php if (($user['medik'] ?? 0) <= 9) { ?>
<div class="tophead2 cntr">
    <div class="hrs"></div>
    <a class="button" href="?loc=4&type=2">
        Train Mastery <img src="/images/book.png" width="17" height="17" alt="Books"> <?php echo $medik; ?>
    </a>
    <div class="hrs"></div>
</div>
<?php } ?>

<div class="tophead">
    <div class="ohh"><img class="img1" src="/images/t3.png" width="50" height="50" alt="Warlock"></div>
    <span class="orange"><b>Warlock Mastery</b></span>
    <small>
        (<?php echo $user['mag'] ?? 0; ?> of 10)<br>
        Increases Warlock ability effects by <span class="lime"><b><?php echo ($user['mag'] ?? 0) * 5; ?>%</b></span>
    </small>
</div>

<?php if (($user['mag'] ?? 0) <= 9) { ?>
<div class="tophead2 cntr">
    <div class="hrs"></div>
    <a class="button" href="?loc=4&type=3">
        Train Mastery <img src="/images/book.png" width="17" height="17" alt="Books"> <?php echo $mag; ?>
    </a>
    <div class="hrs"></div>
</div>
<?php } ?>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>