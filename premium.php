<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (isset($_GET['pay']) && ($user['premium_time'] ?? 0) <= time()) {
    if (($user['gold'] ?? 0) < 600) {
        ruby(null);
        header('Location: /premium.php');
        exit();
    }

    qry("UPDATE `users` SET `gold` = `gold` - ?, `premium` = ?, `premium_time` = ? WHERE `id` = ? LIMIT 1", array(600, 2, time() + 86400 * 3, $uid));
    msg("Premium VIP Account activated for 3 days!");
    header('Location: /premium.php');
    exit();
}

head("Premium VIP Account", $uid);
?>

<div class="tophead">
    <center><span class="yellow"><b>VIP Premium Privileges & Bonuses:</b></span></center>
    <br>
    <li>VIP status active in all game zones</li>
    <li>Duration: 3 full days (72 hours)</li>
    <li>Distinctive golden VIP badge on your profile</li>
    <br>
    <li><span class="lime">+100%</span> Bonus Experience</li>
    <li><span class="lime">+100%</span> Bonus Gold Coins</li>
    <li><span class="lime">+25%</span> Increased Resource drop rate</li>
    <li><span class="lime">+25%</span> Increased Book drop rate</li>
    <br>
    <li><span class="lime">15% Discount</span> on Ability upgrades</li>
    <li><span class="lime">15% Discount</span> on Equipment forging</li>
    <li><span class="lime">15% Discount</span> on Equipment crafting</li>
    <li><span class="lime">15% Discount</span> on Rune crafting</li>
</div>

<?php if (($user['premium_time'] ?? 0) > time()) { ?>
    <div class="tophead cntr"><span class="yellow"><b>Time Remaining: <?php echo timer($user['premium_time']); ?></b></span></div>
<?php } else { ?>
    <div class="tophead cntr">
        <b>3-Day VIP Pass Activation</b><br>
        <div class="hrs"></div>
        <a class="button button_amber" href="?pay">Purchase for <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> 600 Rubies</a>
        <div class="hrs"></div>
    </div>
<?php } ?>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>