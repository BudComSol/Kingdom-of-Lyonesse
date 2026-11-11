<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$sun = number($_GET['sun'] ?? 0);
$type1 = array('', 'metal', 'tkan', 'book', 'kamen', 'swit', 'skills');
$coll1 = array('', 4, 6, 8, 10);

$rand_type = rand(1, 6);
$rand_coll = rand(1, 4);

$type = $type1[$rand_type] ?? 'metal';
$coll = $coll1[$rand_coll] ?? 4;

if ($sun == 1 && ($user['key'] ?? 0) >= 16 && ($user['sun1'] ?? 0) == 0) {
    qry("UPDATE `users` SET `{$type}` = `{$type}` + ?, `sun1` = '1' WHERE `id` = ? LIMIT 1", array($coll, $uid));
    msg("Opening Common Chest, you received: <img src='/images/" . $type . ".png' width='16' height='16'> +" . $coll . " " . ucfirst($type));
    header('Location: /task.php');
    exit();
}

if ($sun == 2 && ($user['key2'] ?? 0) >= 96 && ($user['sun2'] ?? 0) == 0) {
    qry("UPDATE `users` SET `{$type}` = `{$type}` + ?, `sun2` = '1' WHERE `id` = ? LIMIT 1", array($coll * 3, $uid));
    msg("Opening Rare Chest, you received: <img src='/images/" . $type . ".png' width='16' height='16'> +" . ($coll * 3) . " " . ucfirst($type));
    header('Location: /task.php');
    exit();
}

$prem = 1 * max(1, (int)($user['premium'] ?? 1));

if (isset($_GET['ok'])) {
    $id = number($_GET['ok']);
    $quest = fch("SELECT * FROM `quest_user` WHERE `id` = ? AND `id_user` = ? LIMIT 1", array($id, $uid));

    if ($quest) {
        $q = fch("SELECT * FROM `quest` WHERE `id` = ? LIMIT 1", array($quest['id_quest']));
        if ($q && ($quest['koll'] >= $quest['max_koll'] || $quest['koll'] >= $q['koll'])) {
            qry("UPDATE `quest_user` SET `koll` = '0', `last` = ? WHERE `id` = ? LIMIT 1", array(time() + 86400 * 2, $quest['id']));
            $rewardCoins = clanRewardAmount($uid, $q['silver'] * $prem);
            qry("UPDATE `users` SET `gold` = `gold` + ?, `silver` = `silver` + ?, `key` = `key` + 3, `key2` = `key2` + 3, `tour` = `tour` + 1 WHERE `id` = ? LIMIT 1",
                array($q['gold'], $rewardCoins, $uid));
            msg("Quest reward claimed! (+" . $q['gold'] . " Rubies, +" . $rewardCoins . " Coins, +3 Keys, +1 Tournament Point)");
        }
    }
    header('Location: /task.php');
    exit();
}

head("Daily Quests & Chests", $uid);
?>

<table class="tophead" style="width:100%;">
    <tr>
        <td style="width: 50%;" class="cntr">
            <font color="#32cd32"><b>Common Chest</b></font><br>
            <img src="/images/sun1.png" alt="Chest"><br>
            <small>
                <?php if (($user['key'] ?? 0) < 16) { ?>
                    <img class="money" src="/images/key.png" width="16" height="16" alt="Keys"> <?php echo $user['key'] ?? 0; ?> of 16
                <?php } else { ?>
                    <?php if (($user['sun1'] ?? 0) == 0) { ?>
                        <a class="button" style="padding: 4px 12px; font-size: 0.8rem;" href="?sun=1">Open Chest</a>
                    <?php } else { ?>
                        <font color="#888">Claimed for today</font>
                    <?php } ?>
                <?php } ?>
            </small>
        </td>

        <td style="width: 50%;" class="cntr">
            <font color="#a335ee"><b>Rare Chest</b></font><br>
            <img src="/images/sun2.png" alt="Rare Chest"><br>
            <small>
                <?php if (($user['key2'] ?? 0) < 96) { ?>
                    <img class="money" src="/images/key.png" width="16" height="16" alt="Keys"> <?php echo $user['key2'] ?? 0; ?> of 96
                <?php } else { ?>
                    <?php if (($user['sun2'] ?? 0) == 0) { ?>
                        <a class="button button_amber" style="padding: 4px 12px; font-size: 0.8rem;" href="?sun=2">Open Chest</a>
                    <?php } else { ?>
                        <font color="#888">Claimed for today</font>
                    <?php } ?>
                <?php } ?>
            </small>
        </td>
    </tr>
</table>

<?php
$q_stmt = $BaseConnect->prepare("SELECT * FROM `quest_user` WHERE `id_user` = ? ORDER BY `id_quest` DESC, `last` ASC");
$q_stmt->execute([$uid]);

while ($post = $q_stmt->fetch(PDO::FETCH_ASSOC)) {
    $quest = fch("SELECT * FROM `quest` WHERE `id` = ? LIMIT 1", array($post['id_quest']));
    if (!$quest) continue;
?>
    <div class="tophead cntr">
        <font color="silver"><b><?php echo htmlspecialchars($quest['name']); ?></b></font><br>
        <small>
            <font color="#888">
                Rewards:
                <img class="money" src="/images/ruby.png" width="16" height="16" alt="Rubies"> <?php echo $quest['gold']; ?> Rubies &nbsp;
                <img class="money" src="/images/gold.png" width="16" height="16" alt="Coins"> <?php echo $quest['silver'] * $prem; ?> Coins &nbsp;
                <img class="money" src="/images/key.png" width="16" height="16" alt="Keys"> 3 Keys &nbsp;
                <img class="money" src="/images/tour.png" width="16" height="16" alt="Tournament"> +1 Point
                <br>
                <?php if (($post['last'] ?? 0) >= time()) { ?>
                    <span class="lime"><b>Completed for today</b></span>
                <?php } elseif ($post['koll'] >= $quest['koll']) { ?>
                    <span class="yellow"><b>Quest Goal Achieved!</b></span>
                <?php } else { ?>
                    Progress: <b><?php echo $post['koll']; ?> of <?php echo $quest['koll']; ?></b>
                <?php } ?>
            </font>
        </small>
    </div>

    <?php if (($post['last'] ?? 0) < time() && $post['koll'] >= $quest['koll']) { ?>
        <div class="tophead2 cntr">
            <div class="hrs"></div>
            <a href="?ok=<?php echo $post['id']; ?>" class="button">Claim Quest Reward</a>
            <div class="hrs"></div>
        </div>
    <?php } ?>
<?php
}

foot($uid);
?>