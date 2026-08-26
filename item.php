<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$id = number($_GET['id'] ?? $uid);
$us = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));

if (!$us) {
    header('Location: ?id=' . $uid);
    exit();
}

$count = cnt("SELECT (id) FROM `inv` WHERE `yes` = '0' AND `id_user` = ?", array($uid));

// Action: Unequip item
if (isset($_GET['open'])) {
    $id2 = abs(intval($_GET['open']));
    $item2 = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `yes` = '1' AND `id` = ? LIMIT 1", array($uid, $id2));

    if (!$item2) {
        header('Location: ?id=' . $id);
        exit();
    }
    if ($count >= ($user['limit_inv'] ?? 30)) {
        err("Not enough space in backpack to unequip item!");
        header('Location: ?id=' . $id);
        exit();
    }

    qry("UPDATE `users` SET `power` = `power` - ?, `block` = `block` - ?, `health` = `health` - ? WHERE `id` = ? LIMIT 1", array($item2['power'], $item2['block'], $item2['health'], $uid));
    qry("UPDATE `inv` SET `yes` = '0' WHERE `id` = ? LIMIT 1", array($item2['id']));
    msg("Item unequipped successfully!");
    header('Location: ?id=' . $id);
    exit();
}

head("Equipment - " . $us['login'], $uid);

$dat = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$dat2 = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$color = array('1' => '#32cd32', '2' => '#a335ee', '3' => '#ff8000', '4' => '#05CCFF', '5' => '#f5270d');
$type = array('1' => 'Helmet', '2' => 'Shoulders', '3' => 'Armor', '4' => 'Gloves', '5' => 'Sword', '6' => 'Shield', '7' => 'Pants', '8' => 'Boots');

// Calculate Set Resonance
$equippedSets = [];
for ($i = 1; $i <= 8; $i++) {
    $eq = fch("SELECT `set` FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($i, $id));
    if ($eq && isset($eq['set'])) {
        $equippedSets[$eq['set']] = ($equippedSets[$eq['set']] ?? 0) + 1;
    }
}

$activeBonusText = "";
foreach ($equippedSets as $setTier => $setPieceCount) {
    if ($setPieceCount >= 8) {
        $activeBonusText = "<div class='tophead2 cntr' style='border-color: #f1c40f; background: rgba(30, 24, 10, 0.95);'><span class='yellow' style='font-size: 1rem;'><b>⭐ 8/8 Grand Set Resonance Active!</b></span><br><small><span class='lime'>+25% Power, +25% Health, +10% Critical Strike</span></small></div>";
        break;
    } elseif ($setPieceCount >= 4) {
        $activeBonusText = "<div class='tophead2 cntr' style='border-color: #2ecc71; background: rgba(10, 26, 16, 0.95);'><span class='lime' style='font-size: 0.95rem;'><b>✨ 4/8 Set Resonance Active!</b></span><br><small>+10% Power & +10% Health</small></div>";
        break;
    }
}

if (!empty($activeBonusText)) {
    echo $activeBonusText;
}

if (isset($_GET['view'])) {
    $ids = abs(intval($_GET['view']));
    $item = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `yes` = '1' AND `id` = ? LIMIT 1", array($id, $ids));

    if (!$item) {
        header('Location: ?id=' . $id);
        exit();
    }

    $inv = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($item['type'], $uid));

    $param_1 = ($inv['power'] ?? 0) + ($inv['block'] ?? 0) + ($inv['health'] ?? 0);
    $param_2 = ($item['power'] ?? 0) + ($item['block'] ?? 0) + ($item['health'] ?? 0);
    $param = "";

    if ($param_1 > $param_2) {
        $param = "Worse: <font color='red'>-" . ($param_1 - $param_2) . "</font>";
    } elseif ($param_1 < $param_2) {
        $param = "Better: <font color='lime'>+" . ($param_2 - $param_1) . "</font>";
    }
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgItem<?php echo $item['set']; ?>" src="/images/items/<?php echo $item['set']; ?>/<?php echo $item['type']; ?>.png" width="50" height="50" alt="">
        </div>
        <a href="?id=<?php echo $id; ?>&view=<?php echo $item['id']; ?>"><font color="<?php echo $color[$item['set']] ?? '#fff'; ?>"><b><?php echo htmlspecialchars($item['name']); ?></b></font></a>
        <small>(<?php echo $item['smitch']; ?>/<?php echo $item['max_smitch']; ?>)</small>
        <small>
            <br><img src="/images/power.png" width="15px" height="15px"> <?php echo $item['power']; ?>
            <img src="/images/block.png" width="15px" height="15px"> <?php echo $item['block']; ?>
            <img src="/images/health.png" width="15px" height="15px"> <?php echo $item['health']; ?>
            <br><?php echo $param; ?><br>
        </small>
    </div>

    <div class="tophead">
        <img src="/images/type<?php echo $item['set']; ?>.png" alt=""> <font color="<?php echo $color[$item['set']] ?? '#fff'; ?>"><?php echo $dat[$item['set']] ?? 'Common'; ?> Quality</font><br>
        <?php
        if ($item['rune'] >= 1) {
            echo "<img class='img2' src='/images/type" . $item['rune'] . ".png'> <font color='" . ($color[$item['rune']] ?? '#fff') . "'>" . ($dat2[$item['rune']] ?? 'Rare') . " Rune</font> <font color='lime'>+" . ($item['rune_param'] * 3) . "</font>";
        } else {
            echo "<img src='/images/type0.png'> No rune socketed";
        }
        ?>
        <br><img src="/images/shop.png" width="17" height="17"> Forge Level: <a href="/shop.php?loc=2"><span class="orange">+<?php echo $item['smitch']; ?></span></a>
    </div>

    <div class="tophead">
        <img src="/images/power.png" width="15px" height="15px"> Power: <?php echo $item['power']; ?><br>
        <img src="/images/block.png" width="15px" height="15px"> Defense: <?php echo $item['block']; ?><br>
        <img src="/images/health.png" width="15px" height="15px"> Health: <?php echo $item['health']; ?><br>
    </div>

    <?php if ($uid == $id) { ?>
        <div class="tophead2 cntr">
            <a class="button button_danger" href="?id=<?php echo $id; ?>&open=<?php echo $item['id']; ?>">Unequip to Backpack</a>
        </div>
    <?php } ?>

    <a href="/item.php?id=<?php echo $id; ?>" class="tophead cntr">← Back to Equipment</a>
<?php
    foot($uid);
    exit();
}

for ($i = 1; $i <= 8; $i++) {
    $item = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($i, $id));
    $myEquipped = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($i, $uid));

    if ($item) {
        $param_2 = ($item['power'] ?? 0) + ($item['block'] ?? 0) + ($item['health'] ?? 0);
        $param_1 = ($myEquipped['power'] ?? 0) + ($myEquipped['block'] ?? 0) + ($myEquipped['health'] ?? 0);
        $param = "";

        if ($param_1 > $param_2) {
            $param = "Worse: <font color='red'>-" . ($param_1 - $param_2) . "</font>";
        } elseif ($param_1 < $param_2) {
            $param = "Better: <font color='lime'>+" . ($param_2 - $param_1) . "</font>";
        }
?>
        <div class="tophead">
            <div class="ohh">
                <img class="imgItem<?php echo $item['set']; ?>" src="/images/items/<?php echo $item['set']; ?>/<?php echo $item['type']; ?>.png" width="50" height="50" alt="">
            </div>
            <a href="?id=<?php echo $id; ?>&view=<?php echo $item['id']; ?>"><font color="<?php echo $color[$item['set']] ?? '#fff'; ?>"><u><?php echo htmlspecialchars($item['name']); ?></u></font></a>
            <small>(+<?php echo $item['smitch']; ?> Forge)
            <?php if ($uid == $id) { ?>
                <span style="float: right;"><a href="?id=<?php echo $id; ?>&open=<?php echo $item['id']; ?>"><span class="orange">[Unequip]</span></a></span>
            <?php } ?>
            </small>
            <br>
            <img src="/images/power.png" width="15px" height="15px"> Power: <?php echo $item['power']; ?> &nbsp;
            <img src="/images/block.png" width="15px" height="15px"> Defense: <?php echo $item['block']; ?> &nbsp;
            <img src="/images/health.png" width="15px" height="15px"> Health: <?php echo $item['health']; ?><br>
            <small><?php echo $param; ?></small>
        </div>
<?php
    } else {
?>
        <div class="tophead">
            <table>
                <tr>
                    <td class="w1px">
                        <span class="items-list">
                            <span class="items-icons"></span>
                        </span>
                    </td>
                    <td>
                        <b><?php echo $type[$i] ?? 'Slot'; ?></b><br>
                        <small><font color="#888">Empty slot</font></small>
                    </td>
                </tr>
            </table>
        </div>
<?php
    }
}
?>

<a href="/profile.php?id=<?php echo $id; ?>" class="tophead cntr">← Back to Profile</a>

<?php
foot($uid);
?>