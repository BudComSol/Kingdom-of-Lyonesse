<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$dat = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$dat2 = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');
$color = array('1' => '#32cd32', '2' => '#a335ee', '3' => '#ff8000', '4' => '#05CCFF', '5' => '#f5270d');
$case = array('1' => 2, '2' => 4, '3' => 6, '4' => 8, '5' => 10);

// 1. Action: Batch Dismantle by Quality Tier
if (isset($_GET['dismantle_all'])) {
    $tier = number($_GET['dismantle_all']);
    if ($tier >= 1 && $tier <= 5) {
        $matchingItems = $BaseConnect->prepare("SELECT * FROM `inv` WHERE `yes` = '0' AND `id_user` = ? AND `set` = ?");
        $matchingItems->execute([$uid, $tier]);
        $itemsToDel = $matchingItems->fetchAll(PDO::FETCH_ASSOC);
        $totalCount = count($itemsToDel);

        if ($totalCount > 0) {
            $gainPerItem = $case[$tier] ?? 2;
            $totalMetal = $gainPerItem * $totalCount;
            $totalCloth = $gainPerItem * $totalCount;

            qry("UPDATE `users` SET `metal` = `metal` + ?, `tkan` = `tkan` + ? WHERE `id` = ? LIMIT 1", array($totalMetal, $totalCloth, $uid));
            qry("DELETE FROM `inv` WHERE `yes` = '0' AND `id_user` = ? AND `set` = ?", array($uid, $tier));
            msg("Batch dismantled {$totalCount} " . ($dat[$tier] ?? '') . " items! Received +{$totalMetal} Iron and +{$totalCloth} Cloth.");
        } else {
            err("No " . ($dat[$tier] ?? '') . " items found in your backpack to dismantle.");
        }
    }
    header('Location: /bag.php');
    exit();
}

// 2. Action: Dismantle Single Item
if (isset($_GET['del'])) {
    $id = abs(intval($_GET['del']));
    $inv2 = fch("SELECT * FROM `inv` WHERE `yes` = '0' AND `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $id));
    if (!$inv2) {
        header('Location: /bag.php');
        exit();
    }
    $matGain = $case[$inv2['set']] ?? 2;
    qry("UPDATE `users` SET `metal` = `metal` + ?, `tkan` = `tkan` + ? WHERE `id` = ? LIMIT 1", array($matGain, $matGain, $uid));
    qry("DELETE FROM `inv` WHERE `id_user` = ? AND `yes` = '0' AND `id` = ? LIMIT 1", array($uid, $inv2['id']));
    msg("Item dismantled successfully! Received +{$matGain} Iron and +{$matGain} Cloth.");
    header('Location: /bag.php');
    exit();
}

// 3. Action: Equip Item
if (isset($_GET['open'])) {
    $id = abs(intval($_GET['open']));
    $item2 = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `yes` = '0' AND `id` = ? LIMIT 1", array($uid, $id));

    if (!$item2) {
        header('Location: /bag.php');
        exit();
    }

    if (($user['level'] ?? 1) < ($item2['level'] ?? 1)) {
        err("This item requires Level <b>" . $item2['level'] . "</b> to equip!");
        header('Location: /bag.php');
        exit();
    }

    $inv = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($item2['type'], $uid));

    if ($inv) {
        $param = (($user['power'] - $inv['power']) + $item2['power']);
        qry("UPDATE `users` SET `power` = ?, `block` = ?, `health` = ? WHERE `id` = ? LIMIT 1", array($param, $param, $param, $uid));

        // Transfer forge level if upgrading to higher tier
        if ($item2['smitch'] == 0 && $inv['smitch'] >= 1 && $item2['set'] >= $inv['set']) {
            $stat = ($inv['smitch'] * 3);
            qry("UPDATE `inv` SET `power` = `power` + ?, `block` = `block` + ?, `health` = `health` + ?, `smitch` = ? WHERE `id` = ? LIMIT 1", array($stat, $stat, $stat, $inv['smitch'], $item2['id']));
            qry("UPDATE `inv` SET `power` = `power` - ?, `block` = `block` - ?, `health` = `health` - ?, `smitch` = '0' WHERE `id` = ? LIMIT 1", array($stat, $stat, $stat, $inv['id']));
            qry("UPDATE `users` SET `power` = `power` + ?, `block` = `block` + ?, `health` = `health` + ? WHERE `id` = ? LIMIT 1", array($stat, $stat, $stat, $uid));
        }

        // Transfer rune if upgrading
        if ($item2['rune'] == 0 && $inv['rune'] >= 1 && $item2['set'] >= $inv['set']) {
            $stat2 = $inv['rune_param'];
            qry("UPDATE `inv` SET `power` = `power` + ?, `block` = `block` + ?, `health` = `health` + ?, `rune` = ?, `rune_param` = ? WHERE `id` = ? LIMIT 1", array($stat2, $stat2, $stat2, $inv['rune'], $inv['rune_param'], $item2['id']));
            qry("UPDATE `inv` SET `power` = `power` - ?, `block` = `block` - ?, `health` = `health` - ?, `rune` = '0', `rune_param` = '0' WHERE `id` = ? LIMIT 1", array($stat2, $stat2, $stat2, $inv['id']));
            qry("UPDATE `users` SET `power` = `power` + ?, `block` = `block` + ?, `health` = `health` + ? WHERE `id` = ? LIMIT 1", array($stat2, $stat2, $stat2, $uid));
        }

        qry("UPDATE `inv` SET `yes` = '0' WHERE `id` = ? LIMIT 1", array($inv['id']));
    }

    qry("UPDATE `inv` SET `yes` = '1' WHERE `id` = ? LIMIT 1", array($item2['id']));
    msg("Equipped " . htmlspecialchars($item2['name']) . " successfully!");
    header('Location: /bag.php');
    exit();
}

head("Hero Backpack", $uid);

// Confirm Dismantle View
$del_item = abs(intval($_GET['del_item'] ?? 0));
if ($del_item >= 1) {
    $invs = fch("SELECT * FROM `inv` WHERE `yes` = '0' AND `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $del_item));
    if ($invs) {
        $matGain = $case[$invs['set']] ?? 2;
?>
        <div class="tophead2 cntr">
            Do you want to dismantle <font color="<?php echo $color[$invs['set']] ?? '#fff'; ?>"><b><?php echo htmlspecialchars($invs['name']); ?></b></font>?<br>
            You will receive <img src="/images/metal.png" width="17" height="17" alt="Iron"> <?php echo $matGain; ?> Iron and <img src="/images/tkan.png" width="17" height="17" alt="Cloth"> <?php echo $matGain; ?> Cloth<br><br>
            <a class="button" href="?del=<?php echo $del_item; ?>">Yes, dismantle</a> &nbsp; <a class="button button_danger" href="/bag.php">Cancel</a>
        </div>
<?php
    }
}

// Single Item Detailed View
if (isset($_GET['view'])) {
    $id = abs(intval($_GET['view']));
    $item = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `yes` = '0' AND `id` = ? LIMIT 1", array($uid, $id));

    if (!$item) {
        header('Location: /bag.php');
        exit();
    }

    $inv = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($item['type'], $uid));

    $param_1 = ($inv['power'] ?? 0) + ($inv['block'] ?? 0) + ($inv['health'] ?? 0);
    $param_2 = ($item['power'] ?? 0) + ($item['block'] ?? 0) + ($item['health'] ?? 0);
    $param = "";

    if ($param_1 >= $param_2) {
        $param = "Worse: <font color='red'>-" . ($param_1 - $param_2) . "</font>";
    } elseif ($param_1 < $param_2) {
        $param = "Better: <font color='lime'>+" . ($param_2 - $param_1) . "</font>";
    }
?>
    <div class="tophead">
        <div class="ohh">
            <img class="imgItem<?php echo $item['set']; ?>" src="/images/items/<?php echo $item['set']; ?>/<?php echo $item['type']; ?>.png" width="50" height="50" alt="">
        </div>
        <a href="?view=<?php echo $item['id']; ?>"><font color="<?php echo $color[$item['set']] ?? '#fff'; ?>"><b><?php echo htmlspecialchars($item['name']); ?></b></font></a>
        <small>(<?php echo $item['smitch']; ?>/<?php echo $item['max_smitch']; ?>)</small>
        <span style="float: right;"><a href="?view=<?php echo $item['id']; ?>&del_item=<?php echo $item['id']; ?>"><img src="/images/icon_del.png" width="16" height="16" alt="Dismantle"></a></span>
        <small><br><?php echo $param; ?><br></small>
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

    <div class="tophead2 cntr">
        <form action="?open=<?php echo $item['id']; ?>" method="post">
            <input type="submit" value="Equip Item">
        </form>
    </div>

    <a href="/bag.php" class="tophead cntr">← Back to Backpack</a>
<?php
    foot($uid);
    exit();
}

$count = cnt("SELECT (id) FROM `inv` WHERE `yes` = '0' AND `id_user` = ?", array($uid));
$countCommon = cnt("SELECT (id) FROM `inv` WHERE `yes` = '0' AND `id_user` = ? AND `set` = '1'", array($uid));
$countRare = cnt("SELECT (id) FROM `inv` WHERE `yes` = '0' AND `id_user` = ? AND `set` = '2'", array($uid));
?>

<div class="tophead2 cntr" style="padding: 8px;">
    <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
        <?php if ($countCommon > 0) { ?>
            <a class="button button_emerald" style="font-size: 0.8rem; padding: 4px 10px;" href="?dismantle_all=1" onclick="return confirm('Dismantle all <?php echo $countCommon; ?> Common items?');">
                ♻️ Dismantle All Common (<?php echo $countCommon; ?>)
            </a>
        <?php } ?>
        <?php if ($countRare > 0) { ?>
            <a class="button button_violet" style="font-size: 0.8rem; padding: 4px 10px;" href="?dismantle_all=2" onclick="return confirm('Dismantle all <?php echo $countRare; ?> Rare items?');">
                ♻️ Dismantle All Rare (<?php echo $countRare; ?>)
            </a>
        <?php } ?>
    </div>
</div>

<?php
if ($count <= 0) {
    echo '<div class="tophead cntr">Your backpack is empty. Defeat monsters in the Dark Forest to collect loot!</div>';
}

$msg_q = $BaseConnect->prepare("SELECT * FROM `inv` WHERE `id_user` = ? AND `yes` = '0' ORDER BY `set` DESC, `level` DESC");
$msg_q->execute([$uid]);

while ($msg = $msg_q->fetch(PDO::FETCH_ASSOC)) {
    $inv = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($msg['type'], $uid));
    $param_1 = ($inv['power'] ?? 0) + ($inv['block'] ?? 0) + ($inv['health'] ?? 0);
    $param_2 = ($msg['power'] ?? 0) + ($msg['block'] ?? 0) + ($msg['health'] ?? 0);
    $diff = $param_2 - $param_1;
    $param = ($diff >= 0) ? "<span class='lime'><b>+{$diff} Total Stats</b></span>" : "<span class='red'><b>{$diff} Total Stats</b></span>";
?>
    <div class="tophead item-bag-card">
        <div class="ohh">
            <img class="imgItem<?php echo $msg['set']; ?>" src="/images/items/<?php echo $msg['set']; ?>/<?php echo $msg['type']; ?>.png" width="48" height="48" alt="">
        </div>
        <a href="?view=<?php echo $msg['id']; ?>"><font color="<?php echo $color[$msg['set']] ?? '#fff'; ?>"><b><?php echo htmlspecialchars($msg['name']); ?></b></font></a>
        <small>(+<?php echo $msg['smitch']; ?> Forge)</small>
        <span style="float: right;"><a href="?del_item=<?php echo $msg['id']; ?>" title="Dismantle"><img src="/images/icon_del.png" width="16" height="16" alt="Dismantle"></a></span>
        <br><small>Quality: <?php echo $dat[$msg['set']] ?? 'Common'; ?> &nbsp;|&nbsp; Comparison: <?php echo $param; ?></small>
    </div>

    <?php if (($user['level'] ?? 1) >= ($msg['level'] ?? 1)) { ?>
        <div class="tophead2 cntr" style="margin-top: -4px; padding: 4px;">
            <form action="?open=<?php echo $msg['id']; ?>" method="post" style="display: inline;">
                <input type="submit" value="Equip Item" style="padding: 4px 16px; font-size: 0.85rem;">
            </form>
        </div>
    <?php } ?>
<?php
}

foot($uid);
?>