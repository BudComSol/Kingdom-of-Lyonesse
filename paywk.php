<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$packs = array(
    1 => array('name' => 'Pouch of Rubies', 'rubies' => 50, 'bonus_vip' => 0, 'price' => '$1.99', 'icon' => '/images/ruby.png'),
    2 => array('name' => 'Chest of Rubies', 'rubies' => 150, 'bonus_vip' => 0, 'price' => '$4.99', 'icon' => '/images/ruby.png'),
    3 => array('name' => 'Treasury Vault', 'rubies' => 500, 'bonus_vip' => 86400, 'price' => '$14.99', 'icon' => '/images/ruby.png'),
    4 => array('name' => "Dragon's Hoard", 'rubies' => 1200, 'bonus_vip' => 86400 * 3, 'price' => '$29.99', 'icon' => '/images/ruby.png')
);

$go = number($_GET['go'] ?? 0);

// Action: Process / Claim Pack Purchase
if ($go >= 1 && $go <= 4 && isset($_GET['confirm'])) {
    $pack = $packs[$go];
    $rubiesToAdd = $pack['rubies'];
    $vipSeconds  = $pack['bonus_vip'];

    if ($vipSeconds > 0) {
        $newVipTime = max(time(), (int)($user['premium_time'] ?? 0)) + $vipSeconds;
        qry("UPDATE `users` SET `gold` = `gold` + ?, `premium` = 2, `premium_time` = ? WHERE `id` = ? LIMIT 1", array($rubiesToAdd, $newVipTime, $uid));
        msg("Purchase successful! Received +{$rubiesToAdd} Rubies and VIP status extension.");
    } else {
        qry("UPDATE `users` SET `gold` = `gold` + ? WHERE `id` = ? LIMIT 1", array($rubiesToAdd, $uid));
        msg("Purchase successful! Received +{$rubiesToAdd} Rubies.");
    }

    header('Location: /paywk.php');
    exit();
}

// Action: Redeem Promo Code
if (isset($_POST['promo_code'])) {
    $code = strtoupper(trim(checking($_POST['promo_code'] ?? '')));
    if ($code === 'FREE100' || $code === 'LYONESSE' || $code === 'VIP2026') {
        qry("UPDATE `users` SET `gold` = `gold` + 100 WHERE `id` = ? LIMIT 1", array($uid));
        msg("Promo code redeemed successfully! Received +100 Rubies!");
    } else {
        err("Invalid or expired promo code!");
    }
    header('Location: /paywk.php');
    exit();
}

head("Royal Treasury - Buy Rubies", $uid);
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Royal Treasury & Ruby Vault</span></div>
    <div class="hrs"></div>
    <small><font color="#a4b0be">Rubies allow you to acquire legendary gear, socket powerful runes, and activate VIP perks.</font></small>
    <br><br>
    Your Current Balance: <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> <span class="yellow"><b><?php echo (int)($user['gold'] ?? 0); ?> Rubies</b></span>
</div>

<?php
// Detailed Checkout View for selected pack
if ($go >= 1 && $go <= 4 && !isset($_GET['confirm'])) {
    $pack = $packs[$go];
?>
    <div class="tophead2 cntr">
        <span class="yellow" style="font-size: 1.1rem;"><b><?php echo htmlspecialchars($pack['name']); ?></b></span>
        <div class="hrs"></div>
        <img src="<?php echo $pack['icon']; ?>" width="35" height="35" alt="Rubies">
        <br><br>
        Contains: <span class="lime"><b>+<?php echo $pack['rubies']; ?> Rubies</b></span>
        <?php if ($pack['bonus_vip'] > 0) { ?>
            <br>Bonus: <span class="orange"><b>+<?php echo ($pack['bonus_vip'] / 86400); ?> Day(s) VIP Status</b></span>
        <?php } ?>
        <br>Package Tier: <b><?php echo $pack['price']; ?></b>
        <div class="hrs"></div>
        <a class="button button_amber" href="?go=<?php echo $go; ?>&confirm=1">Confirm & Claim Rubies (+<?php echo $pack['rubies']; ?>)</a>
        &nbsp;
        <a class="button button_danger" href="/paywk.php">Cancel</a>
        <div class="hrs"></div>
    </div>
<?php
}
?>

<div class="tophead2 cntr">
    <table style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
        <?php foreach ($packs as $id => $pack) { ?>
            <tr>
                <td style="width: 15%; text-align: center; vertical-align: middle;">
                    <img src="<?php echo $pack['icon']; ?>" width="28" height="28" alt="Rubies">
                </td>
                <td style="width: 55%; text-align: left; vertical-align: middle;">
                    <span class="yellow"><b><?php echo htmlspecialchars($pack['name']); ?></b></span><br>
                    <small>
                        <span class="lime">+<?php echo $pack['rubies']; ?> Rubies</span>
                        <?php if ($pack['bonus_vip'] > 0) { ?>
                            <span class="orange">(+<?php echo ($pack['bonus_vip'] / 86400); ?>d VIP)</span>
                        <?php } ?>
                    </small>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: middle;">
                    <a class="button" style="padding: 5px 12px; font-size: 0.85rem;" href="?go=<?php echo $id; ?>">Get Pack</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Redeem Promo Code</span></div>
    <div class="hrs"></div>
    <form action="/paywk.php" method="post">
        <input type="text" name="promo_code" placeholder="Enter promo code (e.g. LYONESSE)" required style="width: 70%; max-width: 250px; text-transform: uppercase;">
        <br><br>
        <input type="submit" value="Redeem Code">
    </form>
</div>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>
