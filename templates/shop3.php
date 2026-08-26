<?php
$dat = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$dat2 = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$color = array('1' => '#32cd32', '2' => '#a335ee', '3' => '#ff8000', '4' => '#05CCFF', '5' => '#f5270d');
$type = array('1' => 'Helmet', '2' => 'Shoulders', '3' => 'Armor', '4' => 'Gloves', '5' => 'Sword', '6' => 'Shield', '7' => 'Pants', '8' => 'Boots');

$view = abs(intval($_GET['id'] ?? 0));
if ($view >= 1) {
    $item = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `yes` = '1' AND `id` = ? LIMIT 1", array($uid, $view));

    if (!$item) {
        header('Location: ?loc=3');
        exit();
    }

    $run = abs(intval($_GET['rune'] ?? 0));
    if ($run >= 1 && $run <= 5) {
        if (($user['premium_time'] ?? 0) > time()) {
            $case = ($run * 150) - intval(($run * 150) * 15 / 100);
        } else {
            $case = $run * 150;
        }

        $param = $run * 50;

        if (($user['swit'] ?? 0) < $case) {
            err("Not enough scrolls!");
            header('Location: ?loc=3&id=' . $item['id']);
            exit();
        }

        if (($user['kamen'] ?? 0) < $case) {
            err("Not enough stone!");
            header('Location: ?loc=3&id=' . $item['id']);
            exit();
        }

        if ($item['rune'] < $run) {
            qry("UPDATE `inv` SET `power` = `power` + ?, `block` = `block` + ?, `health` = `health` + ?, `rune` = ?, `rune_param` = `rune_param` + ? WHERE `id` = ? LIMIT 1", array($param, $param, $param, $run, $param, $item['id']));
            qry("UPDATE `users` SET `power` = `power` + ?, `block` = `block` + ?, `health` = `health` + ?, `swit` = `swit` - ?, `kamen` = `kamen` - ? WHERE `id` = ? LIMIT 1", array($param, $param, $param, $case, $case, $uid));
            msg("Rune socketed successfully! (+" . $param . " to all attributes)");
        }

        header('Location: ?loc=3&id=' . $item['id']);
        exit();
    }

    head("Anna - Socket Runes", $uid);
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgItem<?php echo $item['set']; ?>" src="/images/items/<?php echo $item['set']; ?>/<?php echo $item['type']; ?>.png" width="50" height="50" alt="">
        </div>
        <font color="<?php echo $color[$item['set']] ?? '#fff'; ?>"><b><?php echo htmlspecialchars($item['name']); ?></b></font>
        <small>(<?php echo $item['smitch']; ?>/<?php echo $item['max_smitch']; ?>)</small>
        <br>
        <small>
            <img src="/images/power.png"> Power: <?php echo $item['power']; ?> &nbsp;
            <img src="/images/block.png"> Defense: <?php echo $item['block']; ?> &nbsp;
            <img src="/images/health.png"> Health: <?php echo $item['health']; ?>
            <br>
            <?php
            if ($item['rune'] >= 1) {
                echo "<img class='img2' src='/images/type" . $item['rune'] . ".png'> <font color='" . ($color[$item['rune']] ?? '#fff') . "'>" . ($dat2[$item['rune']] ?? 'Rare') . " Rune</font> <font color='lime'>+" . ($item['rune_param'] * 3) . "</font>";
            } else {
                echo "<img src='/images/type0.png'> No rune socketed";
            }
            ?>
        </small>
    </div>

    <?php
    for ($i = 1; $i <= 5; $i++) {
        if ($item['rune'] <= ($i - 1)) {
            if (($user['premium_time'] ?? 0) > time()) {
                $case = ($i * 150) - intval(($i * 150) * 15 / 100);
            } else {
                $case = $i * 150;
            }
    ?>
            <div class="tophead cntr">
                <img class="img2" src="/images/type<?php echo $i; ?>.png" alt="">
                <font color="<?php echo $color[$i] ?? '#fff'; ?>"><b><?php echo $dat2[$i] ?? 'Rune'; ?> Rune</b></font>
                <img class="img2" src="/images/type<?php echo $i; ?>.png" alt="">
                <br>
                <small>
                    <font color="lime">+<?php echo $i * 50; ?></font> to all attributes<br>
                    Cost: <img src="/images/swit.png" width="16" height="16" alt="Scrolls"> <?php echo $case; ?> &nbsp;|&nbsp;
                    <img src="/images/kamen.png" width="16" height="16" alt="Stone"> <?php echo $case; ?>
                </small>
            </div>
            <div class="tophead2 cntr">
                <div class="hrs"></div>
                <a class="button button_cyan" href="?loc=3&id=<?php echo $view; ?>&rune=<?php echo $i; ?>">Socket Rune (+<?php echo $i * 50; ?> All Stats)</a>
                <div class="hrs"></div>
            </div>
    <?php
        }
    }

    if ($item['rune'] >= 5) {
        echo '<div class="tophead cntr"><span class="orange">Highest tier rune is already socketed!</span></div>';
    }
    ?>

    <div class="tophead2 cntr">
        <span class="orange"><small>Rune bonuses are permanent. Socket higher tier runes to maximize your power.</small></span>
    </div>
    <a href="?loc=3" class="tophead cntr">← Back to Runesmith</a>
<?php
    foot($uid);
    exit();
}

head("Anna - Runesmith", $uid);
?>

<div class="tophead">
    <div class="ohh"><img class="img4" src="/images/shop3.png" width="50px" height="50px" alt="Anna"></div>
    <span class="lime">Anna (Runesmith)</span><br>
    <small>I am a master of arcane runes. Bring me your equipped gear to imbue them with elemental power!</small>
</div>
<br>

<div class="tophead2 cntr">
    Your Scrolls: <img src="/images/swit.png" width="16" height="16" alt="Scrolls"> <?php echo $user['swit'] ?? 0; ?> &nbsp;|&nbsp;
    Stone: <img src="/images/kamen.png" width="16" height="16" alt="Stone"> <?php echo $user['kamen'] ?? 0; ?>
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
                <img src="/images/power.png"> <?php echo $item['power']; ?> &nbsp;
                <img src="/images/block.png"> <?php echo $item['block']; ?> &nbsp;
                <img src="/images/health.png"> <?php echo $item['health']; ?>
                <br>
                <?php
                if ($item['rune'] >= 1) {
                    echo "<img class='img2' src='/images/type" . $item['rune'] . ".png'> <font color='" . ($color[$item['rune']] ?? '#fff') . "'>" . ($dat2[$item['rune']] ?? 'Rare') . " Rune</font> <font color='lime'>+" . ($item['rune_param'] * 3) . "</font>";
                } else {
                    echo "<img src='/images/type0.png'> No rune socketed";
                }
                ?>
            </small>
        </div>
        <div class="tophead2 cntr">
            <div class="hrs"></div>
            <?php if ($item['rune'] < 5) { ?>
                <a class="button" href="?loc=3&id=<?php echo $item['id']; ?>">Socket Runes</a>
            <?php } else { ?>
                <span class="orange">Max Rune Tier Reached</span>
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