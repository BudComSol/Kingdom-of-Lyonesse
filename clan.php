<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$clanId = (int)($user['id_clan'] ?? 0);

// Action: Create Clan
if (isset($_GET['create']) && $clanId == 0) {
    $clanName = trim(checking($_POST['clan_name'] ?? ''));
    $creationCost = 5000;

    if (mb_strlen($clanName) < 3 || mb_strlen($clanName) > 24) {
        err("Clan name must be between 3 and 24 characters!");
    } elseif (($user['silver'] ?? 0) < $creationCost) {
        err("You need at least 5,000 Silver Coins to establish a new Clan!");
    } else {
        $existing = fch("SELECT * FROM `clans` WHERE `name` = ? LIMIT 1", array($clanName));
        if ($existing) {
            err("A Clan with this name already exists!");
        } else {
            qry("UPDATE `users` SET `silver` = `silver` - ? WHERE `id` = ? LIMIT 1", array($creationCost, $uid));
            qry("INSERT INTO `clans` (`name`, `leader_id`, `level`, `exp`, `gold`, `created_at`) VALUES (?, ?, 1, 0, 0, ?)",
                array($clanName, $uid, time()));
            $newClanId = (int)$BaseConnect->lastInsertId();

            qry("INSERT INTO `clan_members` (`clan_id`, `user_id`, `rank`, `joined_at`) VALUES (?, ?, 3, ?)",
                array($newClanId, $uid, time()));
            qry("UPDATE `users` SET `id_clan` = ? WHERE `id` = ? LIMIT 1", array($newClanId, $uid));

            // Initialize Clan Raid Boss
            qry("INSERT INTO `clan_boss` (`clan_id`, `boss_name`, `hp`, `max_hp`, `reward_gold`, `reward_exp`, `last_attack`) VALUES (?, ?, 25000, 25000, 5000, 2500, 0)",
                array($newClanId, 'Ancient Abyssal Leviathan'));

            msg("Clan '{$clanName}' established successfully! You are now the Clan Leader.");
            header('Location: /clan.php');
            exit();
        }
    }
    header('Location: /clan.php');
    exit();
}

// Action: Join Clan
if (isset($_GET['join']) && $clanId == 0) {
    $targetClanId = abs(intval($_GET['join']));
    $targetClan = fch("SELECT * FROM `clans` WHERE `id` = ? LIMIT 1", array($targetClanId));

    if ($targetClan) {
        qry("INSERT INTO `clan_members` (`clan_id`, `user_id`, `rank`, `joined_at`) VALUES (?, ?, 1, ?)",
            array($targetClanId, $uid, time()));
        qry("UPDATE `users` SET `id_clan` = ? WHERE `id` = ? LIMIT 1", array($targetClanId, $uid));
        msg("Joined clan '{$targetClan['name']}' successfully!");
    }
    header('Location: /clan.php');
    exit();
}

// Action: Leave Clan
if (isset($_GET['leave']) && $clanId > 0) {
    $member = fch("SELECT * FROM `clan_members` WHERE `clan_id` = ? AND `user_id` = ? LIMIT 1", array($clanId, $uid));
    if ($member && $member['rank'] == 3) {
        err("Clan Leaders must transfer leadership or disband the clan before leaving!");
    } else {
        qry("DELETE FROM `clan_members` WHERE `clan_id` = ? AND `user_id` = ? LIMIT 1", array($clanId, $uid));
        qry("UPDATE `users` SET `id_clan` = 0 WHERE `id` = ? LIMIT 1", array($uid));
        msg("You have left the clan.");
    }
    header('Location: /clan.php');
    exit();
}

// Action: Attack Clan Raid Boss
if (isset($_GET['attack_boss']) && $clanId > 0) {
    $clanBoss = fch("SELECT * FROM `clan_boss` WHERE `clan_id` = ? LIMIT 1", array($clanId));
    if ($clanBoss && $clanBoss['hp'] > 0) {
        $lastAttack = (int)($clanBoss['last_attack'] ?? 0);
        $combatStats = effectiveCombatStats($user);
        $heroDmg = rand(max(10, round($combatStats['power'] / 2)), max(20, $combatStats['power']));

        $newHp = max(0, $clanBoss['hp'] - $heroDmg);
        qry("UPDATE `clan_boss` SET `hp` = ?, `last_attack` = ? WHERE `clan_id` = ? LIMIT 1", array($newHp, time(), $clanId));

        if ($newHp <= 0) {
            // Distribute rewards to all clan members
            $members = $BaseConnect->prepare("SELECT `user_id` FROM `clan_members` WHERE `clan_id` = ?");
            $members->execute([$clanId]);
            $allMembers = $members->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allMembers as $m) {
                $memberCoins = clanRewardAmount((int)$m['user_id'], (int)$clanBoss['reward_gold']);
                $memberExp = clanRewardAmount((int)$m['user_id'], (int)$clanBoss['reward_exp']);
                qry("UPDATE `users` SET `silver` = `silver` + ?, `exp` = `exp` + ? WHERE `id` = ? LIMIT 1",
                    array($memberCoins, $memberExp, $m['user_id']));
            }

            // Level up clan
            qry("UPDATE `clans` SET `exp` = `exp` + 500, `level` = `level` + 1 WHERE `id` = ? LIMIT 1", array($clanId));
            // Respawn stronger raid boss
            $nextMax = $clanBoss['max_hp'] + 15000;
            qry("UPDATE `clan_boss` SET `hp` = ?, `max_hp` = ?, `reward_gold` = `reward_gold` + 2500, `reward_exp` = `reward_exp` + 1000 WHERE `clan_id` = ? LIMIT 1",
                array($nextMax, $nextMax, $clanId));

            msg("⭐ RAID BOSS SLAIN! Clan members received +{$clanBoss['reward_gold']} Coins and +{$clanBoss['reward_exp']} Exp! Clan Level increased!");
        } else {
            msg("⚔️ Struck Clan Raid Boss for {$heroDmg} damage!");
        }
    }
    header('Location: /clan.php');
    exit();
}

head("Guild & Clan Castle", $uid);

// ---------------------------------------------------------------------
// VIEW: NO CLAN (Create or Browse)
// ---------------------------------------------------------------------
if ($clanId == 0) {
?>
    <div class="tophead cntr">
        <div class="title cntr"><span class="color">Guilds & Clan Castles</span></div>
        <div class="hrs"></div>
        <small><font color="#a4b0be">Unite with fellow knights, unlock passive clan bonuses, and conquer massive weekly Raid Bosses!</font></small>
    </div>

    <div class="tophead cntr">
        <span class="yellow" style="font-size: 1.05rem;"><b>Establish a New Clan</b></span>
        <div class="hrs"></div>
        <form action="/clan.php?create" method="post">
            Clan Name:<br>
            <input type="text" name="clan_name" required minlength="3" maxlength="24" placeholder="Enter clan name..." style="width: 70%; max-width: 250px;"><br><br>
            <small>Creation Cost: <img src="/images/gold.png" width="15px" height="15px"> <span class="yellow">5,000 Silver Coins</span></small><br><br>
            <input type="submit" value="Found Clan">
        </form>
    </div>

    <div class="tophead cntr">
        <span class="yellow" style="font-size: 1.05rem;"><b>Active Clans</b></span>
        <div class="hrs"></div>
    </div>

    <?php
    $clansQuery = $BaseConnect->query("SELECT c.*, COUNT(m.id) as member_count FROM `clans` c LEFT JOIN `clan_members` m ON c.id = m.clan_id GROUP BY c.id ORDER BY c.level DESC, c.exp DESC LIMIT 15");
    $clans = $clansQuery->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clans)) {
        echo '<div class="tophead2 cntr">No clans established yet. Be the first knight to found a clan!</div>';
    } else {
        foreach ($clans as $c) {
            $leader = fch("SELECT `login` FROM `users` WHERE `id` = ? LIMIT 1", array($c['leader_id']));
    ?>
            <div class="tophead" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span class="yellow" style="font-size: 1rem;"><b>⚔️ <?php echo htmlspecialchars($c['name']); ?></b></span>
                    <small>(Lvl <?php echo $c['level']; ?>)</small><br>
                    <small>Leader: <?php echo htmlspecialchars($leader['login'] ?? 'Unknown'); ?> &nbsp;|&nbsp; Members: <?php echo $c['member_count']; ?></small>
                </div>
                <div>
                    <a class="button button_emerald" style="padding: 4px 12px; font-size: 0.85rem;" href="?join=<?php echo $c['id']; ?>">Join Clan</a>
                </div>
            </div>
    <?php
        }
    }
    ?>

    <a href="/index.php" class="tophead cntr">← Back to Town Square</a>
<?php
    foot($uid);
    exit();
}

// ---------------------------------------------------------------------
// VIEW: IN A CLAN (Clan Hall, Raid Boss, Roster)
// ---------------------------------------------------------------------
$clan = fch("SELECT * FROM `clans` WHERE `id` = ? LIMIT 1", array($clanId));
$clanMembersCount = cnt("SELECT (id) FROM `clan_members` WHERE `clan_id` = ?", array($clanId));
$myMember = fch("SELECT * FROM `clan_members` WHERE `clan_id` = ? AND `user_id` = ? LIMIT 1", array($clanId, $uid));
$clanBoss = fch("SELECT * FROM `clan_boss` WHERE `clan_id` = ? LIMIT 1", array($clanId));
$bossHpPercent = ($clanBoss && $clanBoss['max_hp'] > 0) ? round(($clanBoss['hp'] / $clanBoss['max_hp']) * 100, 1) : 0;
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">🏰 Clan Hall: <?php echo htmlspecialchars($clan['name']); ?></span></div>
    <div class="hrs"></div>
    Clan Level: <span class="yellow"><b><?php echo $clan['level']; ?></b></span> &nbsp;|&nbsp;
    Members: <span class="lime"><b><?php echo $clanMembersCount; ?></b></span> &nbsp;|&nbsp;
    Clan Buff: <span class="gold"><b>+<?php echo min(25, $clan['level'] * 5); ?>% Bonus Exp & Coins</b></span>
</div>

<!-- Weekly Clan Raid Boss -->
<?php if ($clanBoss) { ?>
    <div class="tophead2 cntr" style="border: 1px solid #e74c3c; background: rgba(36, 14, 18, 0.95);">
        <span class="red" style="font-size: 1.05rem;"><b>👹 Weekly Clan Raid Boss</b></span><br>
        <span class="yellow"><b><?php echo htmlspecialchars($clanBoss['boss_name']); ?></b></span><br><br>
        
        <div class="progress-bar exp" style="height: 14px; margin: 6px auto; width: 85%;">
            <div style="width:<?php echo $bossHpPercent; ?>%;" class="progress _red"></div>
        </div>
        <small class="lime"><b><?php echo max(0, $clanBoss['hp']); ?> / <?php echo $clanBoss['max_hp']; ?> HP</b> (<?php echo $bossHpPercent; ?>%)</small>
        <br><br>
        Reward Pool: <img src="/images/gold.png" width="15px" height="15px"> <span class="yellow">+<?php echo $clanBoss['reward_gold']; ?> Coins</span> &nbsp;|&nbsp;
        <img src="/images/exp.png" width="15px" height="15px"> <span class="lime">+<?php echo $clanBoss['reward_exp']; ?> Exp</span> to each member!
        <div class="hrs"></div>
        <a class="button button_danger" style="font-size: 0.95rem; padding: 6px 20px;" href="?attack_boss=1">⚔️ Attack Raid Boss</a>
    </div>
<?php } ?>

<!-- Clan Members Roster -->
<div class="tophead">
    <span class="yellow"><b>Clan Members Roster:</b></span>
</div>

<?php
$membersQuery = $BaseConnect->prepare("SELECT m.*, u.login, u.level, u.power FROM `clan_members` m INNER JOIN `users` u ON m.user_id = u.id WHERE m.clan_id = ? ORDER BY m.rank DESC, u.level DESC");
$membersQuery->execute([$clanId]);
$membersList = $membersQuery->fetchAll(PDO::FETCH_ASSOC);

$ranks = [1 => 'Member', 2 => 'Officer', 3 => 'Leader'];

foreach ($membersList as $m) {
?>
    <div class="tophead" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span class="lime"><b><?php echo htmlspecialchars($m['login']); ?></b></span>
            <small>(Lvl <?php echo $m['level']; ?> - Power: <?php echo $m['power']; ?>)</small>
        </div>
        <div>
            <span class="orange" style="font-size: 0.8rem;"><?php echo $ranks[$m['rank']] ?? 'Member'; ?></span>
        </div>
    </div>
<?php } ?>

<div class="tophead2 cntr">
    <a class="button button_violet" href="/chat.php">💬 Clan & World Chat</a> &nbsp;
    <?php if ($myMember['rank'] < 3) { ?>
        <a class="button button_danger" href="?leave=1" onclick="return confirm('Are you sure you want to leave this clan?');">Leave Clan</a>
    <?php } ?>
</div>

<a href="/index.php" class="tophead cntr">← Back to Town Square</a>

<?php
foot($uid);
?>
