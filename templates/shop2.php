<?php
$dat = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$dat2 = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$color = array('1' => '#32cd32', '2' => '#a335ee', '3' => '#ff8000', '4' => '#05CCFF', '5' => '#f5270d');
$type = array('1' => 'Helmet', '2' => 'Shoulders', '3' => 'Armor', '4' => 'Gloves', '5' => 'Sword', '6' => 'Shield', '7' => 'Pants', '8' => 'Boots');

$smitch_level = array(
    '0' => 5, '1' => 1000, '2' => 5000, '3' => 10, '4' => 10000, '5' => 25000,
    '6' => 50, '7' => 50000, '8' => 75000, '9' => 100, '10' => 100000, '11' => 125000,
    '12' => 150, '13' => 150000, '14' => 175000, '15' => 250, '16' => 200000, '17' => 225000,
    '18' => 350, '19' => 250000, '20' => 1000, '21' => 275000, '22' => 300000, '23' => 500,
    '24' => 350000, '25' => 400000, '26' => 700, '27' => 450000, '28' => 500000, '29' => 1000,
    '30' => 750000, '31' => 750000, '32' => 1000000, '33' => 1250, '34' => 1000000, '35' => 1000000,
    '36' => 1500, '37' => 1000000, '38' => 1000000, '39' => 1500, '40' => 1000000, '41' => 1000000,
    '42' => 1000000, '43' => 1500, '44' => 1000000, '45' => 1000000, '46' => 1500, '47' => 1000000,
    '48' => 1000000, '49' => 1500, '50' => 1000000
);

$case_level = array(
    '0' => 'gold', '1' => 'silver', '2' => 'silver', '3' => 'gold', '4' => 'silver', '5' => 'silver',
    '6' => 'gold', '7' => 'silver', '8' => 'silver', '9' => 'gold', '10' => 'silver', '11' => 'silver',
    '12' => 'gold', '13' => 'silver', '14' => 'silver', '15' => 'gold', '16' => 'silver', '17' => 'silver',
    '18' => 'gold', '19' => 'silver', '20' => 'gold', '21' => 'silver', '22' => 'silver', '23' => 'gold',
    '24' => 'silver', '25' => 'silver', '26' => 'gold', '27' => 'silver', '28' => 'silver', '29' => 'gold',
    '30' => 'silver', '31' => 'silver', '32' => 'silver', '33' => 'gold', '34' => 'silver', '35' => 'silver',
    '36' => 'gold', '37' => 'silver', '38' => 'silver', '39' => 'gold', '40' => 'silver', '41' => 'silver',
    '42' => 'silver', '43' => 'gold', '44' => 'silver', '45' => 'silver', '46' => 'gold', '47' => 'silver',
    '48' => 'silver', '49' => 'gold', '50' => 'silver'
);

$id = number($_GET['id'] ?? 0);
if ($id >= 1) {
    $sm = fch("SELECT * FROM `inv` WHERE `yes` = '1' AND `id` = ? AND `id_user` = ? LIMIT 1", array($id, $uid));

    if (!$sm || ($sm['smitch'] ?? 0) >= ($sm['max_smitch'] ?? 0)) {
        header('Location: ?loc=2');
        exit();
    }

    if (($user['premium_time'] ?? 0) > time()) {
        $costValue = $smitch_level[$sm['smitch']] ?? 1000;
        $gold = $costValue - intval($costValue * 15 / 100);
        $silver = $costValue - intval($costValue * 15 / 100);
    } else {
        $gold = $smitch_level[$sm['smitch']] ?? 1000;
        $silver = $smitch_level[$sm['smitch']] ?? 1000;
    }

    // Process Forge Upgrade Action
    if (isset($_GET['ok'])) {
        $currencyType = $case_level[$sm['smitch']] ?? 'silver';

        if ($currencyType === 'gold') {
            if (($user['gold'] ?? 0) < $gold) {
                ruby(null);
                header('Location: ?loc=2&id=' . $sm['id']);
                exit();
            }
            qry("UPDATE `users` SET `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($gold, $uid));
        } else {
            if (($user['silver'] ?? 0) < $silver) {
                err("Not enough gold coins!");
                header('Location: ?loc=2&id=' . $sm['id']);
                exit();
            }
            qry("UPDATE `users` SET `silver` = `silver` - ? WHERE `id` = ? LIMIT 1", array($silver, $uid));
        }

        $kamenReq = $sm['smitch'] + 1;
        if (($user['kamen'] ?? 0) < $kamenReq) {
            err("Not enough crafting stone!");
            header('Location: ?loc=2&id=' . $sm['id']);
            exit();
        }

        qry("UPDATE `users` SET `power` = `power` + 3, `block` = `block` + 3, `health` = `health` + 3, `kamen` = `kamen` - ? WHERE `id` = ? LIMIT 1", array($kamenReq, $uid));
        qry("UPDATE `inv` SET `power` = `power` + 3, `block` = `block` + 3, `health` = `health` + 3, `smitch` = `smitch` + 1 WHERE `id` = ? LIMIT 1", array($sm['id']));

        msg("Equipment successfully forged to +" . ($sm['smitch'] + 1) . "!");
        header('Location: ?loc=2&id=' . $sm['id']);
        exit();
    }

    head("Blacksmith - Forge Item", $uid);
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgItem<?php echo $sm['set']; ?>" src="/images/items/<?php echo $sm['set']; ?>/<?php echo $sm['type']; ?>.png" width="50" height="50" alt="">
        </div>
        <font color="<?php echo $color[$sm['set']] ?? '#fff'; ?>"><b><?php echo htmlspecialchars($sm['name']); ?></b></font>
        <small>(<?php echo $sm['smitch']; ?>/<?php echo $sm['max_smitch']; ?>)</small>
        <br>
        <small>
            <img src="/images/power.png"> Power: <?php echo $sm['power']; ?> &nbsp;
            <img src="/images/block.png"> Defense: <?php echo $sm['block']; ?> &nbsp;
            <img src="/images/health.png"> Health: <?php echo $sm['health']; ?>
            <br>
            <?php
            if ($sm['rune'] >= 1) {
                echo "<img class='img2' src='/images/type" . $sm['rune'] . ".png'> <font color='" . ($color[$sm['rune']] ?? '#fff') . "'>" . ($dat2[$sm['rune']] ?? 'Rare') . " Rune</font> <font color='lime'>+" . ($sm['rune_param'] * 3) . "</font>";
            } else {
                echo "<img src='/images/type0.png'> No rune socketed";
            }
            ?>
        </small>
    </div>

    <div class="tophead cntr">
        Upgrade Forge to <span class="orange"><b>+<?php echo $sm['smitch'] + 1; ?></b></span><br>
        Cost:
        <?php if (($case_level[$sm['smitch']] ?? 'silver') === 'gold') { ?>
            <img src="/images/ruby.png" width="16" height="16"> <?php echo $gold; ?> Rubies
        <?php } else { ?>
            <img src="/images/gold.png" width="16" height="16"> <?php echo number_format($silver); ?> Coins
        <?php } ?>
        and <img src="/images/kamen.png" width="16" height="16"> <?php echo $sm['smitch'] + 1; ?> Stone
    </div>

    <div class="tophead2 cntr">
        <div class="hrs"></div>
        <a class="button button_amber" href="?loc=2&id=<?php echo $sm['id']; ?>&ok">🔨 Forge & Upgrade (+3 Stats)</a>
        <div class="hrs"></div>
    </div>

    <a href="/shop.php?loc=2" class="tophead cntr">← Back to Blacksmith</a>
<?php
    foot($uid);
    exit();
}

head("Edward - Blacksmith", $uid);
?>

<div class="tophead">
    <div class="ohh"><img class="img4" src="/images/shop2.png" width="50px" height="50px" alt="Edward"></div>
    <span class="lime">Edward (Blacksmith)</span><br>
    <small>Greetings! Bring me your equipped weapons and armor, and I will forge them to greater power!</small>
</div>
<br>

<div class="tophead2 cntr">
    Your Coins: <img src="/images/gold.png" width="16" height="16"> <?php echo number_format((int)($user['silver'] ?? 0)); ?> &nbsp;|&nbsp;
    Stone: <img src="/images/kamen.png" width="16" height="16"> <?php echo $user['kamen'] ?? 0; ?>
</div>

<?php
for ($i = 1; $i <= 8; $i++) {
    $item = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($i, $uid));
    if ($item) {
?>
        <div class="tophead">
            <div class="ohh">
                <img class="imgItem<?php echo $item['set']; ?>" src="/images/items/<?php echo $item['set']; ?>/<?php echo $item['type']; ?>.png" width="50" height="50" alt="">
            </div>
            <font color="<?php echo $color[$item['set']] ?? '#fff'; ?>"><u><?php echo htmlspecialchars($item['name']); ?></u></font>
            <small>(<?php echo $item['smitch']; ?>/<?php echo $item['max_smitch']; ?>)</small>
            <br>
            <small>
                <img src="/images/power.png"> Power: <?php echo $item['power']; ?> &nbsp;
                <img src="/images/block.png"> Defense: <?php echo $item['block']; ?> &nbsp;
                <img src="/images/health.png"> Health: <?php echo $item['health']; ?>
                <br>
                Forge Level: <span class="orange"><b>+<?php echo $item['smitch']; ?></b></span>
            </small>
        </div>
        <div class="tophead2 cntr">
            <div class="hrs"></div>
            <?php if ($item['smitch'] < $item['max_smitch']) { ?>
                <a class="button" href="?loc=2&id=<?php echo $item['id']; ?>">Forge Item</a>
            <?php } else { ?>
                <span class="orange">Max Forge Level Reached (+<?php echo $item['max_smitch']; ?>)</span>
            <?php } ?>
            <div class="hrs"></div>
        </div>
<?php
    }
}
?>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>