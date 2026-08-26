<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$energy = abs(intval($_GET['coll'] ?? 0));

if ($energy == 2 && ($user['arena'] ?? 0) <= 0 && ($user['gold'] ?? 0) >= 10) {
    qry("UPDATE `users` SET `arena` = 20, `gold` = `gold` - 10 WHERE `id` = ? LIMIT 1", array($uid));
    msg("Arena energy refilled (+20 Battles)!");
    header('Location: /arena.php');
    exit();
}

$prem = (($user['premium_time'] ?? 0) > time()) ? 2 : 1;
$rand_sun = (($user['premium_time'] ?? 0) > time()) ? rand(1, 4) : rand(1, 6);

$rand_bot = rand(1, 7);
$rand_param = rand(5, 15);
$rand_param2 = rand(5, 15);
$rand_param_end = rand(1, 2);
$rand_end = rand(1, 3);

$res_rand_win  = intval(rand(25, 50) * $prem);
$res_rand_lose = intval(rand(15, 25) * $prem);

$name = array(
    '',
    'Barbarian',
    'Wizard',
    'Warlock',
    'Half-Demon',
    'Rogue',
    'Ranger',
    'Paladin'
);

$type1 = array('', 'metal', 'tkan', 'book', 'kamen', 'swit');
$coll1 = array('', '1', '2', '1', '2');

$rand_type = rand(1, 5);
$rand_coll = rand(1, 4);

$type = $type1[$rand_type] ?? 'metal';
$coll = (int)($coll1[$rand_coll] ?? 1);
$name_bot = $name[$rand_bot] ?? 'Gladiator';

if (isset($_GET['attack']) && ($user['arena'] ?? 0) >= 1) {
    if ($rand_end == 1 || $rand_end == 3) { // Victory
        $resource = "";
        if ($rand_sun == 2) {
            qry("UPDATE `users` SET `{$type}` = `{$type}` + ? WHERE `id` = ? LIMIT 1", array($coll, $uid));
            $resource = "<img src='/images/" . $type . ".png' width='16' height='16'> +" . $coll . " " . ucfirst($type);
        }

        $q_8 = fch("SELECT * FROM `quest_user` WHERE `id_quest` = ? AND `id_user` = ? LIMIT 1", array(8, $uid));
        if ($q_8 && ($q_8['last'] ?? 0) < time()) {
            qry("UPDATE `quest_user` SET `koll` = `koll` + 1 WHERE `id` = ? LIMIT 1", array($q_8['id']));
        }

        qry("UPDATE `users` SET `exp` = `exp` + ?, `silver` = `silver` + ?, `tour` = `tour` + 1, `arena` = `arena` - 1 WHERE `id` = ? LIMIT 1", array(intval($res_rand_win / 2), $res_rand_win, $uid));

        msg("<center><b>Victory in the Arena!</b><br>" . $resource . " <img src='/images/gold.png' width='15px' height='15px'> +" . intval($res_rand_win) . " Coins &nbsp; <img src='/images/exp.png' width='15px' height='15px'> +" . intval($res_rand_win / 2) . " Exp &nbsp; <img src='/images/tour.png' width='15px' height='15px'> +1 Tournament Point</center>");
    } else { // Defeat
        qry("UPDATE `users` SET `exp` = `exp` + ?, `silver` = `silver` + ?, `arena` = `arena` - 1 WHERE `id` = ? LIMIT 1", array(intval($res_rand_lose / 2), $res_rand_lose, $uid));

        err("<center><b>Defeat in the Arena!</b><br>Consolation: <img src='/images/gold.png' width='15px' height='15px'> +" . intval($res_rand_lose) . " Coins &nbsp; <img src='/images/exp.png' width='15px' height='15px'> +" . intval($res_rand_lose / 2) . " Exp</center>");
    }

    qry("UPDATE `users` SET `arena_tav` = `arena_tav` + 1 WHERE `id` = ? LIMIT 1", array($uid));

    $q_7 = fch("SELECT * FROM `quest_user` WHERE `id_quest` = ? AND `id_user` = ? LIMIT 1", array(7, $uid));
    if ($q_7 && ($q_7['last'] ?? 0) < time()) {
        qry("UPDATE `quest_user` SET `koll` = `koll` + 1 WHERE `id` = ? LIMIT 1", array($q_7['id']));
    }

    header('Location: /arena.php');
    exit();
}

head("PvP Arena", $uid);

if ($energy == 1 && ($user['arena'] ?? 0) <= 0) {
?>
    <div class="tophead2 cntr">
        Do you want to purchase 20 arena battles?<br>
        <font color="#888">Cost: <img src="/images/ruby.png" width="17" height="17" alt="Ruby"> 10 Rubies</font><br><br>
        <a class="button" href="?coll=2">Yes, purchase (+20)</a> &nbsp; <a class="button button_danger" href="/arena.php">Cancel</a>
    </div>
<?php
}

if (($user['arena'] ?? 0) <= 0 && $energy != 1) {
    echo "<div class='tophead2 cntr'><span class='yellow'>Battles automatically replenish every 30 minutes.</span></div>";
}
?>

<div class="tophead2">
    <div class="tophead cntr">
        <table style="width:100%;">
            <tr>
                <td colspan="2" class="cntr">
                    <span class="tomato" style="font-size: 1.1rem; font-weight: bold;"><?php echo $name_bot; ?></span>
                    <div class="hrs"></div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;" class="cntr">
                    <img class="imgSkill round_image" src="/images/arena/<?php echo $rand_bot; ?>.png" width="110" height="110" alt="Opponent">
                </td>
                <td style="width: 50%;" class="cntr">
                    <?php
                    $params = ($rand_param_end == 1) ? max(1, ($user['power'] ?? 10) - $rand_param) : (($user['power'] ?? 10) + $rand_param2);
                    ?>
                    <img src="/images/power.png" width="17" height="17" alt="Power"> Power: <?php echo $params; ?><br>
                    <img src="/images/block.png" width="17" height="17" alt="Defense"> Defense: <?php echo $params; ?><br>
                    <img src="/images/health.png" width="17" height="17" alt="Health"> Health: <?php echo $params; ?><br><br>
                    <?php if (($user['arena'] ?? 0) >= 1) { ?>
                        <a class="button" href="?attack">⚔ Attack!</a>
                    <?php } else { ?>
                        <a class="button button_amber" href="?coll=1">Refill Energy</a>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="tophead2 cntr">
    <small>
        Available Battles: <b><?php echo $user['arena'] ?? 0; ?> / 20</b>
        <?php if (($user['arena'] ?? 0) <= 0) { ?>
            <a href="?coll=1"><span class="orange">(Refill Now)</span></a>
        <?php } ?>
        <br>
        <font color="#888">The Arena is a competitive combat ground where knights test their martial prowess.</font>
    </small>
</div>

<?php
foot($uid);
?>