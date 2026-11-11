<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$achievementsList = array(
    'first_blood' => array(
        'name' => 'First Blood',
        'desc' => 'Win your first Dark Forest monster battle',
        'title' => 'Novice Adventurer',
        'goal' => 1,
        'current' => (int)($user['kills_boss'] ?? 0),
        'reward_coins' => 500,
        'reward_rubies' => 0
    ),
    'beast_slayer' => array(
        'name' => 'Beast Hunter',
        'desc' => 'Defeat 10 monsters in the Dark Forest',
        'title' => 'Beast Slayer',
        'goal' => 10,
        'current' => (int)($user['kills_boss'] ?? 0),
        'reward_coins' => 2000,
        'reward_rubies' => 5
    ),
    'dragon_nemesis' => array(
        'name' => 'Dragon Nemesis',
        'desc' => 'Defeat 50 monsters in the Dark Forest',
        'title' => 'Dragon Nemesis',
        'goal' => 50,
        'current' => (int)($user['kills_boss'] ?? 0),
        'reward_coins' => 10000,
        'reward_rubies' => 25
    ),
    'gladiator' => array(
        'name' => 'Gladiator',
        'desc' => 'Win 5 PvP Arena battles',
        'title' => 'Gladiator',
        'goal' => 5,
        'current' => (int)($user['tour'] ?? 0),
        'reward_coins' => 1500,
        'reward_rubies' => 5
    ),
    'arena_champion' => array(
        'name' => 'Arena Champion',
        'desc' => 'Win 25 PvP Arena battles',
        'title' => 'Arena Champion',
        'goal' => 25,
        'current' => (int)($user['tour'] ?? 0),
        'reward_coins' => 8000,
        'reward_rubies' => 20
    ),
    'master_forger' => array(
        'name' => 'Master Forger',
        'desc' => 'Forge or upgrade equipment in the Blacksmith',
        'title' => 'Master Blacksmith',
        'goal' => 5,
        'current' => (int)($user['forged_items'] ?? 1),
        'reward_coins' => 2500,
        'reward_rubies' => 10
    ),
    'wealth_accumulator' => array(
        'name' => 'Wealth Accumulator',
        'desc' => 'Accumulate 10,000 Silver Coins',
        'title' => 'Prosperous Knight',
        'goal' => 10000,
        'current' => (int)($user['silver'] ?? 0),
        'reward_coins' => 1000,
        'reward_rubies' => 5
    ),
    'loyal_veteran' => array(
        'name' => 'Loyal Veteran',
        'desc' => 'Reach a 7-day daily login streak',
        'title' => 'Lyonesse Veteran',
        'goal' => 7,
        'current' => (int)($user['daily_streak'] ?? 0),
        'reward_coins' => 5000,
        'reward_rubies' => 15
    ),
    'clan_vanguard' => array(
        'name' => 'Clan Vanguard',
        'desc' => 'Join or lead a Guild / Clan',
        'title' => 'Clan Vanguard',
        'goal' => 1,
        'current' => (($user['id_clan'] ?? 0) > 0) ? 1 : 0,
        'reward_coins' => 2000,
        'reward_rubies' => 5
    ),
    'hero_legend' => array(
        'name' => 'Legendary Hero',
        'desc' => 'Reach Hero Level 5 or higher',
        'title' => 'Legend of Lyonesse',
        'goal' => 5,
        'current' => (int)($user['level'] ?? 1),
        'reward_coins' => 20000,
        'reward_rubies' => 50
    )
);

// Action: Equip Title
if (isset($_GET['equip_title'])) {
    $titleToEquip = checking($_GET['equip_title']);
    qry("UPDATE `users` SET `title` = ? WHERE `id` = ? LIMIT 1", array($titleToEquip, $uid));
    msg("Equipped Hero Title: '{$titleToEquip}'!");
    header('Location: /achievements.php');
    exit();
}

// Action: Claim Achievement Reward
if (isset($_GET['claim'])) {
    $achKey = checking($_GET['claim']);
    if (isset($achievementsList[$achKey])) {
        $ach = $achievementsList[$achKey];
        $isComplete = ($ach['current'] >= $ach['goal']);

        $userAch = fch("SELECT * FROM `achievements_user` WHERE `user_id` = ? AND `achievement_id` = ? LIMIT 1", array($uid, $achKey));
        $isClaimed = ($userAch && $userAch['unlocked'] == 1);

        if ($isComplete && !$isClaimed) {
            qry("INSERT INTO `achievements_user` (`user_id`, `achievement_id`, `progress`, `unlocked`, `unlocked_at`) VALUES (?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE `unlocked` = 1, `unlocked_at` = ?",
                array($uid, $achKey, $ach['current'], time(), time()));

            $coins = clanRewardAmount($uid, $ach['reward_coins']);
            $rubies = $ach['reward_rubies'];
            qry("UPDATE `users` SET `silver` = `silver` + ?, `gold` = `gold` + ? WHERE `id` = ? LIMIT 1", array($coins, $rubies, $uid));

            msg("Achievement Unlocked: '{$ach['name']}'! Claimed +{$coins} Coins and +{$rubies} Rubies. Title unlocked: '{$ach['title']}'!");
        }
    }
    header('Location: /achievements.php');
    exit();
}

head("Hero Achievements & Titles", $uid);
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Hero Achievements & Renowned Titles</span></div>
    <div class="hrs"></div>
    <small><font color="#a4b0be">Complete heroic deeds across Lyonesse to unlock prestige titles and treasure rewards!</font></small>
    <br><br>
    Active Title: <span class="yellow"><b><?php echo !empty($user['title']) ? htmlspecialchars($user['title']) : 'No title equipped'; ?></b></span>
</div>

<div style="display: flex; flex-direction: column; gap: 8px; margin: 10px 0;">
    <?php foreach ($achievementsList as $key => $ach) {
        $userAch = fch("SELECT * FROM `achievements_user` WHERE `user_id` = ? AND `achievement_id` = ? LIMIT 1", array($uid, $key));
        $isClaimed = ($userAch && $userAch['unlocked'] == 1);
        $isComplete = ($ach['current'] >= $ach['goal']);
        $progressPercent = min(100, round(($ach['current'] / max(1, $ach['goal'])) * 100));
        $isEquipped = (!empty($user['title']) && $user['title'] === $ach['title']);
    ?>
        <div class="tophead" style="border-left: 4px solid <?php echo $isClaimed ? '#f1c40f' : ($isComplete ? '#2ecc71' : '#4b6584'); ?>;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span class="yellow" style="font-size: 0.95rem;"><b><?php echo htmlspecialchars($ach['name']); ?></b></span>
                    <br><small><font color="#ced6e0"><?php echo htmlspecialchars($ach['desc']); ?></font></small>
                    <br><small>Title Unlocked: <span class="lime">«<?php echo htmlspecialchars($ach['title']); ?>»</span></small>
                </div>
                <div style="text-align: right; min-width: 100px;">
                    <?php if ($isClaimed) { ?>
                        <span class="lime" style="font-size: 0.8rem;">✓ Unlocked</span><br>
                        <?php if ($isEquipped) { ?>
                            <span class="gold" style="font-size: 0.75rem;">[Active Title]</span>
                        <?php } else { ?>
                            <a class="button" style="padding: 2px 8px; font-size: 0.75rem; margin-top: 4px;" href="?equip_title=<?php echo urlencode($ach['title']); ?>">Equip Title</a>
                        <?php } ?>
                    <?php } elseif ($isComplete) { ?>
                        <a class="button button_amber" style="padding: 4px 10px; font-size: 0.8rem;" href="?claim=<?php echo $key; ?>">Claim Reward</a>
                    <?php } else { ?>
                        <small><font color="#888"><?php echo $ach['current']; ?> / <?php echo $ach['goal']; ?></font></small>
                    <?php } ?>
                </div>
            </div>

            <div class="progress-bar exp" style="height: 6px; margin-top: 6px;">
                <div style="width:<?php echo $progressPercent; ?>%;" class="progress _blue"></div>
            </div>
        </div>
    <?php } ?>
</div>

<a href="/profile.php" class="tophead cntr">← Back to Hero Profile</a>

<?php
foot($uid);
?>
