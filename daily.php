<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$dailyRewards = array(
    1 => array('day' => 1, 'name' => 'Recruit Tribute', 'coins' => 500, 'rubies' => 0, 'metal' => 5, 'tkan' => 5, 'vip' => 0, 'icon' => '/images/gold.png'),
    2 => array('day' => 2, 'name' => "Squire's Bounty", 'coins' => 1000, 'rubies' => 2, 'metal' => 10, 'tkan' => 10, 'vip' => 0, 'icon' => '/images/metal.png'),
    3 => array('day' => 3, 'name' => "Warrior's Spoils", 'coins' => 2000, 'rubies' => 5, 'metal' => 15, 'tkan' => 15, 'vip' => 0, 'icon' => '/images/ruby.png'),
    4 => array('day' => 4, 'name' => "Knight's Cache", 'coins' => 3500, 'rubies' => 10, 'metal' => 20, 'tkan' => 20, 'vip' => 0, 'icon' => '/images/key.png'),
    5 => array('day' => 5, 'name' => 'Champion Vault', 'coins' => 5000, 'rubies' => 15, 'metal' => 30, 'tkan' => 30, 'vip' => 0, 'icon' => '/images/shop.png'),
    6 => array('day' => 6, 'name' => 'Royal Treasury', 'coins' => 7500, 'rubies' => 25, 'metal' => 50, 'tkan' => 50, 'vip' => 86400, 'icon' => '/images/vip.png'),
    7 => array('day' => 7, 'name' => 'Mythic Dragon Hoard', 'coins' => 15000, 'rubies' => 50, 'metal' => 100, 'tkan' => 100, 'vip' => 86400 * 2, 'icon' => '/images/cup.png')
);

$lastClaimTime = (int)($user['daily_last_claim'] ?? 0);
$currentStreak = (int)($user['daily_streak'] ?? 0);

$todayStart = strtotime('today midnight');
$yesterdayStart = strtotime('yesterday midnight');

$canClaimToday = false;
$nextDay = 1;

if ($lastClaimTime < $todayStart) {
    $canClaimToday = true;
    if ($lastClaimTime >= $yesterdayStart && $currentStreak >= 1 && $currentStreak < 7) {
        $nextDay = $currentStreak + 1;
    } else {
        $nextDay = ($currentStreak >= 7) ? 1 : (($lastClaimTime >= $yesterdayStart) ? $currentStreak + 1 : 1);
    }
} else {
    $canClaimToday = false;
    $nextDay = ($currentStreak >= 1 && $currentStreak <= 7) ? $currentStreak : 1;
}

// Action: Claim Today's Daily Reward
if (isset($_GET['claim']) && $canClaimToday) {
    $reward = $dailyRewards[$nextDay];

    $coins = clanRewardAmount($uid, $reward['coins']);
    $rubies = $reward['rubies'];
    $metal = $reward['metal'];
    $tkan = $reward['tkan'];
    $vipSeconds = $reward['vip'];

    $vipSql = "";
    if ($vipSeconds > 0) {
        $newVipTime = max(time(), (int)($user['premium_time'] ?? 0)) + $vipSeconds;
        qry("UPDATE `users` SET `premium` = 2, `premium_time` = ? WHERE `id` = ? LIMIT 1", array($newVipTime, $uid));
    }

    qry("UPDATE `users` SET `silver` = `silver` + ?, `gold` = `gold` + ?, `metal` = `metal` + ?, `tkan` = `tkan` + ?, `daily_streak` = ?, `daily_last_claim` = ? WHERE `id` = ? LIMIT 1",
        array($coins, $rubies, $metal, $tkan, $nextDay, time(), $uid));

    msg("Claimed Day {$nextDay} Reward: +{$coins} Coins, +{$rubies} Rubies, +{$metal} Iron/Cloth!");
    header('Location: /daily.php');
    exit();
}

head("Daily Login Rewards", $uid);
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Daily Login Streak Calendar</span></div>
    <div class="hrs"></div>
    <small><font color="#a4b0be">Log in every day to claim progressive riches, crafting materials, rubies, and VIP time!</font></small>
    <br><br>
    Current Streak: <span class="yellow"><b><?php echo $currentStreak; ?> / 7 Days</b></span>
</div>

<div class="daily-calendar-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; margin: 10px 0;">
    <?php foreach ($dailyRewards as $dayNum => $r) {
        $displayCoins = clanRewardAmount($uid, $r['coins']);
        $isCurrent = ($dayNum === $nextDay && $canClaimToday);
        $isClaimed = ($dayNum <= $currentStreak && !$canClaimToday) || ($dayNum < $nextDay && $canClaimToday);
        $cardBorder = $isCurrent ? 'border: 2px solid #f1c40f; box-shadow: 0 0 15px rgba(241, 196, 15, 0.4);' : ($isClaimed ? 'border: 1px solid #2ecc71; opacity: 0.8;' : 'border: 1px solid var(--border-gold); opacity: 0.6;');
        $bgGradient = $isCurrent ? 'background: linear-gradient(180deg, rgba(40, 32, 16, 0.95), rgba(20, 16, 8, 0.95));' : 'background: rgba(16, 22, 32, 0.95);';
    ?>
        <div class="tophead2 cntr" style="<?php echo $cardBorder; ?> <?php echo $bgGradient; ?> padding: 10px 6px; border-radius: 8px;">
            <span class="yellow"><b>Day <?php echo $dayNum; ?></b></span><br>
            <img src="<?php echo $r['icon']; ?>" width="32" height="32" alt="Reward" style="margin: 4px 0;"><br>
            <small><b><?php echo htmlspecialchars($r['name']); ?></b></small><br>
            <small class="lime">+<?php echo $displayCoins; ?> Coins<?php if ($displayCoins !== $r['coins']) { ?> (with clan bonus)<?php } ?></small><br>
            <?php if ($r['rubies'] > 0) { ?><small class="orange">+<?php echo $r['rubies']; ?> Rubies</small><br><?php } ?>
            <?php if ($r['vip'] > 0) { ?><small class="gold">+<?php echo ($r['vip'] / 86400); ?>d VIP</small><br><?php } ?>
            
            <div style="margin-top: 6px;">
                <?php if ($isClaimed) { ?>
                    <span class="lime" style="font-size: 0.8rem;">✓ Claimed</span>
                <?php } elseif ($isCurrent) { ?>
                    <a class="button button_amber" style="padding: 4px 10px; font-size: 0.8rem;" href="?claim=1">Claim Now</a>
                <?php } else { ?>
                    <span style="font-size: 0.75rem; color: #888;">Locked</span>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

<?php if ($canClaimToday) { ?>
    <div class="tophead2 cntr">
        <a class="button button_amber" style="font-size: 1rem; padding: 8px 24px;" href="?claim=1">🎁 Claim Day <?php echo $nextDay; ?> Reward</a>
    </div>
<?php } else { ?>
    <div class="tophead2 cntr">
        <span class="lime">✓ You have already claimed today's reward!</span><br>
        <small><font color="#888">Next daily reward unlocks tomorrow at midnight.</font></small>
    </div>
<?php } ?>

<a href="/index.php" class="tophead cntr">← Back to Town Square</a>

<?php
foot($uid);
?>
