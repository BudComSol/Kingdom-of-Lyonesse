<?php
$type = number($_GET['type'] ?? 0);
if ($type >= 1 && $type <= 5) {
    $type_color = array('1' => '#32cd32', '2' => '#a335ee', '3' => '#ff8000', '4' => '#05CCFF', '5' => '#f5270d');
    $dat = array('1' => 'Common', '2' => 'Rare', '3' => 'Epic', '4' => 'Legendary', '5' => 'Mythic');

    // 1. Process Craft / Purchase Action
    if (isset($_GET['pay'])) {
        $id = abs(intval($_GET['pay']));
        $item = fch("SELECT * FROM `items` WHERE `id` = ? LIMIT 1", array($id));

        if (!$item) {
            header('Location: ?loc=1&type=' . $type);
            exit();
        }

        $count = cnt("SELECT (id) FROM `inv` WHERE `yes` = '0' AND `id_user` = ?", array($uid));
        if ($count >= ($user['limit_inv'] ?? 30)) {
            err("Not enough space in your backpack!");
            header('Location: ?loc=1&type=' . $item['set']);
            exit();
        }

        if (($user['premium_time'] ?? 0) > time()) {
            $gold1 = intval($item['gold'] * 15 / 100);
            $gold = $item['gold'] - $gold1;
            $res1 = intval($item['res'] * 15 / 100);
            $res = $item['res'] - $res1;
        } else {
            $gold = $item['gold'];
            $res = $item['res'];
        }

        if ($item['set'] == 5) {
            if (($user['gold'] ?? 0) < $gold) {
                ruby(null);
                header('Location: ?loc=1&type=' . $item['set']);
                exit();
            }
            qry("UPDATE `users` SET `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($gold, $uid));
        } else {
            if (($user['metal'] ?? 0) < $res || ($user['tkan'] ?? 0) < $res) {
                err("Not enough crafting resources (Iron & Cloth)!");
                header('Location: ?loc=1&type=' . $item['set']);
                exit();
            }
            qry("UPDATE `users` SET `metal` = `metal` - ?, `tkan` = `tkan` - ? WHERE `id` = ? LIMIT 1", array($res, $res, $uid));
        }

        qry("INSERT INTO `inv` SET `id_user` = ?, `set` = ?, `power` = ?, `block` = ?, `health` = ?, `smitch` = '0', `max_smitch` = ?, `yes` = '0', `type` = ?, `name` = ?, `level` = ?", 
            array($uid, $item['set'], $item['power'], $item['block'], $item['health'], $item['max_smitch'], $item['type'], $item['name'], $item['level']));

        msg("Item purchased and placed in your backpack!");
        header('Location: ?loc=1&type=' . $item['set']);
        exit();
    }

    // 2. Process Detailed View
    if (isset($_GET['view'])) {
        $id = abs(intval($_GET['view']));
        $item = fch("SELECT * FROM `items` WHERE `id` = ? LIMIT 1", array($id));

        if (!$item) {
            header('Location: ?loc=1&type=' . $type);
            exit();
        }

        head($item['name'] . " - Equipment Merchant", $uid);

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
            <font color="<?php echo $type_color[$item['set']] ?? '#fff'; ?>"><?php echo htmlspecialchars($item['name']); ?></font>
            <small>(0/<?php echo $item['max_smitch']; ?>)</small>
            <small><br><?php echo $param; ?><br></small>
        </div>

        <div class="tophead">
            <img src="/images/type<?php echo $item['set']; ?>.png" alt=""> <font color="<?php echo $type_color[$item['set']] ?? '#fff'; ?>"><?php echo $dat[$item['set']] ?? 'Common'; ?> Quality</font><br>
            <img src="/images/shop.png" width="17" height="17"> Forge Level: <a href="/shop.php?loc=2"><span class="orange">+0</span></a>
        </div>

        <div class="tophead">
            <img src="/images/power.png" width="15px" height="15px"> Power: <?php echo $item['power']; ?><br>
            <img src="/images/block.png" width="15px" height="15px"> Defense: <?php echo $item['block']; ?><br>
            <img src="/images/health.png" width="15px" height="15px"> Health: <?php echo $item['health']; ?><br>
        </div>

        <a href="/shop.php?loc=1&type=<?php echo $item['set']; ?>" class="tophead cntr">← Back to Equipment Set</a>
<?php
        foot($uid);
        exit();
    }

    $setNames = array(
        1 => 'Recruit Set (Level 1+)',
        2 => 'Warrior Set (Level 5+)',
        3 => 'Knight Set (Level 10+)',
        4 => 'Chosen Set (Level 15+)',
        5 => 'Legendary Set (Level 20+)'
    );

    head("Equipment Merchant - " . ($dat[$type] ?? 'Set'), $uid);
?>
    <div class="tophead cntr"><span class="color"><?php echo $setNames[$type] ?? 'Equipment Set'; ?></span></div>
    <div class="tophead2 cntr">
        Your Resources:
        <img src="/images/metal.png" width="16" height="16" alt="Iron"> <?php echo $user['metal'] ?? 0; ?>
        <img src="/images/tkan.png" width="16" height="16" alt="Cloth"> <?php echo $user['tkan'] ?? 0; ?>
        <img src="/images/ruby.png" width="16" height="16" alt="Rubies"> <?php echo $user['gold'] ?? 0; ?>
    </div>

<?php
    $items_q = $BaseConnect->prepare("SELECT * FROM `items` WHERE `set` = ? ORDER BY `id` ASC");
    $items_q->execute([$type]);

    while ($item = $items_q->fetch(PDO::FETCH_ASSOC)) {
        $inv = fch("SELECT * FROM `inv` WHERE `type` = ? AND `yes` = '1' AND `id_user` = ? LIMIT 1", array($item['type'], $uid));
        $param_1 = ($inv['power'] ?? 0) + ($inv['block'] ?? 0) + ($inv['health'] ?? 0);
        $param_2 = ($item['power'] ?? 0) + ($item['block'] ?? 0) + ($item['health'] ?? 0);
        $param = "";

        if ($param_1 >= $param_2) {
            $param = "Worse: <font color='red'>-" . ($param_1 - $param_2) . "</font>";
        } elseif ($param_1 < $param_2) {
            $param = "Better: <font color='lime'>+" . ($param_2 - $param_1) . "</font>";
        }

        if (($user['premium_time'] ?? 0) > time()) {
            $gold = $item['gold'] - intval($item['gold'] * 15 / 100);
            $res = $item['res'] - intval($item['res'] * 15 / 100);
        } else {
            $gold = $item['gold'];
            $res = $item['res'];
        }
?>
        <div class="tophead">
            <div class="ohh">
                <img class="imgItem<?php echo $item['set']; ?>" src="/images/items/<?php echo $item['set']; ?>/<?php echo $item['type']; ?>.png" width="50" height="50" alt="">
            </div>
            <a href="?loc=1&type=<?php echo $type; ?>&view=<?php echo $item['id']; ?>"><font color="<?php echo $type_color[$item['set']] ?? '#fff'; ?>"><u><?php echo htmlspecialchars($item['name']); ?></u></font></a>
            <small>(0/<?php echo $item['max_smitch']; ?>)</small>
            <br>
            <img src="/images/power.png" width="15px" height="15px"> Power: <?php echo $item['power']; ?> &nbsp;
            <img src="/images/block.png" width="15px" height="15px"> Defense: <?php echo $item['block']; ?> &nbsp;
            <img src="/images/health.png" width="15px" height="15px"> Health: <?php echo $item['health']; ?>
            <br><small><?php echo $param; ?></small>
            <br>
            Cost:
            <?php if ($item['set'] == 5) { ?>
                <img src="/images/ruby.png" width="16" height="16"> <?php echo $gold; ?> Rubies
            <?php } else { ?>
                <img src="/images/metal.png" width="16" height="16"> <?php echo $res; ?> Iron &nbsp;
                <img src="/images/tkan.png" width="16" height="16"> <?php echo $res; ?> Cloth
            <?php } ?>
        </div>
        <div class="tophead2 cntr">
            <div class="hrs"></div>
            <a class="button" href="?loc=1&type=<?php echo $type; ?>&pay=<?php echo $item['id']; ?>">Craft Item</a>
            <div class="hrs"></div>
        </div>
<?php
    }
?>
    <a href="/shop.php?loc=1" class="tophead cntr">← Back to Equipment Sets</a>
<?php
    foot($uid);
    exit();
}

head("Luther - Equipment Merchant", $uid);
?>

<div class="tophead">
    <div class="ohh"><img class="img4" src="/images/shop1.png" width="50px" height="50px" alt="Luther"></div>
    <span class="lime">Luther (Equipment Merchant)</span><br>
    <small>Welcome, warrior! Choose an equipment set suited for your rank and level.</small>
</div>
<br>

<div class="tophead2">
    <a href="?loc=1&type=1"><img src="/images/type1.png"> <span class="green">Recruit Set (Level 1+)</span></a><br>
    <a href="?loc=1&type=2"><img src="/images/type2.png"> <span class="purple">Warrior Set (Level 5+)</span></a><br>
    <a href="?loc=1&type=3"><img src="/images/type3.png"> <span class="orange">Knight Set (Level 10+)</span></a><br>
    <a href="?loc=1&type=4"><img src="/images/type4.png"> <span class="blue">Chosen Set (Level 15+)</span></a><br>
    <a href="?loc=1&type=5"><img src="/images/type5.png"> <span class="red">Legendary Set (Level 20+)</span></a>
</div>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>