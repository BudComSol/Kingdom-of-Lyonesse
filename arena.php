<?php
$location = true;
require_once __DIR__ . "/core/func.php";
require_once __DIR__ . "/core/arena_combat.php";

$arena = (int)($user['arena'] ?? 0);
$energy = abs((int)($_GET['coll'] ?? 0));

if ($energy === 2 && $arena <= 0 && (int)($user['gold'] ?? 0) >= 10) {
    qry("UPDATE `users` SET `arena` = 20, `gold` = `gold` - 10 WHERE `id` = ? AND `arena` <= 0 AND `gold` >= 10 LIMIT 1", [$uid]);
    msg("Arena energy refilled (+20 Battles)!");
    header('Location: /arena.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = checking($_POST['action'] ?? '');
    $opponentId = (int)($_POST['opponent_id'] ?? 0);
    $challengeId = (int)($_POST['challenge_id'] ?? 0);

    try {
        if ($action === 'challenge') {
            $opponent = fch(
                "SELECT `id`, `level` FROM `users` WHERE `id` = ? AND `id` <> ? AND `start` >= 4 LIMIT 1",
                [$opponentId, $uid]
            );
            if (!$opponent || abs((int)$opponent['level'] - (int)$user['level']) > 5) {
                throw new RuntimeException("Choose an active player within 5 levels of your hero.");
            }
            if ($arena <= 0) {
                throw new RuntimeException("You need an available Arena battle to challenge a player.");
            }

            $recentChallenge = fch(
                "SELECT `id` FROM `arena_challenges` WHERE `challenger_id` = ? AND `opponent_id` = ? AND `created_at` >= ? LIMIT 1",
                [$uid, $opponentId, time() - 86400]
            );
            if ($recentChallenge) {
                throw new RuntimeException("You have already challenged this player in the last 24 hours.");
            }

            $stats = effectiveCombatStats($user);
            $statsJson = json_encode($stats);
            if ($statsJson === false) {
                throw new RuntimeException("Could not save your combat stats.");
            }

            $BaseConnect->beginTransaction();
            $consumeEnergy = $BaseConnect->prepare("UPDATE `users` SET `arena` = `arena` - 1 WHERE `id` = ? AND `arena` > 0");
            $consumeEnergy->execute([$uid]);
            if ($consumeEnergy->rowCount() !== 1) {
                throw new RuntimeException("You need an available Arena battle to challenge a player.");
            }
            $insertChallenge = $BaseConnect->prepare(
                "INSERT INTO `arena_challenges` (`challenger_id`, `opponent_id`, `challenger_stats`, `status`, `battle_log`, `created_at`) VALUES (?, ?, ?, 'pending', '', ?)"
            );
            $insertChallenge->execute([$uid, $opponentId, $statsJson, time()]);
            $BaseConnect->commit();
            msg("Challenge sent. One Arena battle was used.");
        } elseif ($action === 'accept' || $action === 'reject') {
            $BaseConnect->beginTransaction();
            $loadChallenge = $BaseConnect->prepare(
                "SELECT * FROM `arena_challenges` WHERE `id` = ? AND `opponent_id` = ? AND `status` = 'pending' FOR UPDATE"
            );
            $loadChallenge->execute([$challengeId, $uid]);
            $challenge = $loadChallenge->fetch(PDO::FETCH_ASSOC);
            if (!$challenge) {
                throw new RuntimeException("This challenge is no longer available.");
            }

            if ($action === 'reject') {
                $reject = $BaseConnect->prepare("UPDATE `arena_challenges` SET `status` = 'rejected', `resolved_at` = ? WHERE `id` = ?");
                $reject->execute([time(), $challengeId]);
                $refund = $BaseConnect->prepare("UPDATE `users` SET `arena` = LEAST(20, `arena` + 1) WHERE `id` = ?");
                $refund->execute([(int)$challenge['challenger_id']]);
                $BaseConnect->commit();
                msg("Challenge declined. The challenger’s Arena battle was refunded.");
            } elseif ((int)$challenge['created_at'] < time() - 172800) {
                $expire = $BaseConnect->prepare("UPDATE `arena_challenges` SET `status` = 'expired', `resolved_at` = ? WHERE `id` = ?");
                $expire->execute([time(), $challengeId]);
                $refund = $BaseConnect->prepare("UPDATE `users` SET `arena` = LEAST(20, `arena` + 1) WHERE `id` = ?");
                $refund->execute([(int)$challenge['challenger_id']]);
                $BaseConnect->commit();
                msg("This challenge expired. The challenger’s Arena battle was refunded.");
            } else {
                $challenger = fch("SELECT `id`, `login`, `level` FROM `users` WHERE `id` = ? LIMIT 1", [(int)$challenge['challenger_id']]);
                if (!$challenger || abs((int)$challenger['level'] - (int)$user['level']) > 5) {
                    throw new RuntimeException("This player is no longer within your Arena level range.");
                }

                $challengerStats = json_decode($challenge['challenger_stats'], true);
                if (!is_array($challengerStats) || !isset($challengerStats['power'], $challengerStats['block'], $challengerStats['health'])) {
                    throw new RuntimeException("The saved challenge stats are invalid.");
                }

                $result = resolveArenaDuel($challengerStats, effectiveCombatStats($user));
                $winnerId = $result['winner'] === 1
                    ? (int)$challenge['challenger_id']
                    : ($result['winner'] === 2 ? $uid : 0);
                $resultLog = "Challenger: {$challenger['login']} (remaining HP: {$result['challenger_hp']})\n"
                    . "Opponent: " . ($user['login'] ?? 'Player') . " (remaining HP: {$result['opponent_hp']})\n"
                    . $result['log'];

                $finishChallenge = $BaseConnect->prepare(
                    "UPDATE `arena_challenges` SET `status` = 'resolved', `winner_id` = ?, `battle_log` = ?, `resolved_at` = ? WHERE `id` = ? AND `status` = 'pending'"
                );
                $finishChallenge->execute([$winnerId, $resultLog, time(), $challengeId]);
                if ($finishChallenge->rowCount() !== 1) {
                    throw new RuntimeException("This challenge was already resolved.");
                }

                $challengerRewardCoins = clanRewardAmount((int)$challenge['challenger_id'], $winnerId === (int)$challenge['challenger_id'] ? 40 : 20);
                $challengerRewardExp = clanRewardAmount((int)$challenge['challenger_id'], $winnerId === (int)$challenge['challenger_id'] ? 40 : 20);
                $opponentRewardCoins = clanRewardAmount($uid, $winnerId === $uid ? 40 : 20);
                $opponentRewardExp = clanRewardAmount($uid, $winnerId === $uid ? 40 : 20);

                $updateRewards = $BaseConnect->prepare(
                    "UPDATE `users` SET `silver` = `silver` + ?, `exp` = `exp` + ?, `arena_tav` = `arena_tav` + 1, `tour` = `tour` + ?, `kills_pvp` = `kills_pvp` + ? WHERE `id` = ?"
                );
                $updateRewards->execute([$challengerRewardCoins, $challengerRewardExp, $winnerId === (int)$challenge['challenger_id'] ? 1 : 0, $winnerId === (int)$challenge['challenger_id'] ? 1 : 0, (int)$challenge['challenger_id']]);
                $updateRewards->execute([$opponentRewardCoins, $opponentRewardExp, $winnerId === $uid ? 1 : 0, $winnerId === $uid ? 1 : 0, $uid]);
                $BaseConnect->commit();

                $challengerName = htmlspecialchars($challenger['login'], ENT_QUOTES, 'UTF-8');
                msg($winnerId === 0 ? "The Arena duel ended in a draw." : ($winnerId === $uid ? "Victory! You defeated {$challengerName}." : "Defeat. {$challengerName} won the duel."));
            }
        } else {
            throw new RuntimeException("Unknown Arena action.");
        }
    } catch (Throwable $error) {
        if ($BaseConnect->inTransaction()) {
            $BaseConnect->rollBack();
        }
        err($error->getMessage());
    }

    header('Location: /arena.php');
    exit();
}

$BaseConnect->beginTransaction();
$expiredQuery = $BaseConnect->prepare(
    "SELECT `id`, `challenger_id` FROM `arena_challenges` WHERE `status` = 'pending' AND `created_at` < ? FOR UPDATE"
);
$expiredQuery->execute([time() - 172800]);
$expiredChallenges = $expiredQuery->fetchAll(PDO::FETCH_ASSOC);
$expireChallenge = $BaseConnect->prepare(
    "UPDATE `arena_challenges` SET `status` = 'expired', `resolved_at` = ? WHERE `id` = ? AND `status` = 'pending'"
);
$refundExpired = $BaseConnect->prepare("UPDATE `users` SET `arena` = LEAST(20, `arena` + 1) WHERE `id` = ?");
foreach ($expiredChallenges as $expired) {
    $expireChallenge->execute([time(), (int)$expired['id']]);
    if ($expireChallenge->rowCount() === 1) {
        $refundExpired->execute([(int)$expired['challenger_id']]);
    }
}
$BaseConnect->commit();

$incomingQuery = $BaseConnect->prepare(
    "SELECT c.*, u.`login`, u.`level` FROM `arena_challenges` c INNER JOIN `users` u ON u.`id` = c.`challenger_id` WHERE c.`opponent_id` = ? AND c.`status` = 'pending' ORDER BY c.`created_at` DESC LIMIT 10"
);
$incomingQuery->execute([$uid]);
$incomingChallenges = $incomingQuery->fetchAll(PDO::FETCH_ASSOC);

$outgoingQuery = $BaseConnect->prepare(
    "SELECT c.*, u.`login` FROM `arena_challenges` c INNER JOIN `users` u ON u.`id` = c.`opponent_id` WHERE c.`challenger_id` = ? AND c.`status` = 'pending' ORDER BY c.`created_at` DESC LIMIT 10"
);
$outgoingQuery->execute([$uid]);
$outgoingChallenges = $outgoingQuery->fetchAll(PDO::FETCH_ASSOC);

$historyQuery = $BaseConnect->prepare(
    "SELECT c.*, a.`login` AS `challenger_login`, o.`login` AS `opponent_login` FROM `arena_challenges` c LEFT JOIN `users` a ON a.`id` = c.`challenger_id` LEFT JOIN `users` o ON o.`id` = c.`opponent_id` WHERE (c.`challenger_id` = ? OR c.`opponent_id` = ?) AND c.`status` IN ('resolved', 'rejected', 'expired') ORDER BY c.`resolved_at` DESC LIMIT 10"
);
$historyQuery->execute([$uid, $uid]);
$history = $historyQuery->fetchAll(PDO::FETCH_ASSOC);

$level = (int)($user['level'] ?? 1);
$playersQuery = $BaseConnect->prepare(
    "SELECT `id`, `login`, `level`, `power`, `block`, `health` FROM `users` WHERE `id` <> ? AND `start` >= 4 AND `level` BETWEEN ? AND ? AND `time_on` >= ? ORDER BY ABS(`level` - ?), `time_on` DESC LIMIT 10"
);
$playersQuery->execute([$uid, max(1, $level - 5), $level + 5, time() - 2592000, $level]);
$players = $playersQuery->fetchAll(PDO::FETCH_ASSOC);

head("Player Arena", $uid);
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Player Arena</span></div>
    <div class="hrs"></div>
    Challenge a real player within 5 levels. The duel uses the challenger’s stats when sent and your current stats when accepted.
    <br><br>
    Available Battles: <b><?php echo $arena; ?> / 20</b>
    <?php if ($arena <= 0) { ?>
        <br><a class="button button_amber" href="?coll=1">Refill Energy</a>
    <?php } ?>
</div>

<?php if ($energy === 1 && $arena <= 0) { ?>
    <div class="tophead2 cntr">
        Refill 20 Arena battles for 10 Rubies?<br><br>
        <a class="button" href="?coll=2">Yes, refill</a>
        <a class="button button_danger" href="/arena.php">Cancel</a>
    </div>
<?php } ?>

<div class="tophead2">
    <div class="title cntr"><span class="color">Available Opponents</span></div>
    <?php if (!$players) { ?>
        <div class="cntr">No active players within your level range are available right now.</div>
    <?php } else { foreach ($players as $player) {
        $opponentStats = effectiveCombatStats($player);
    ?>
        <div class="tophead">
            <a href="/profile.php?id=<?php echo (int)$player['id']; ?>"><b><?php echo htmlspecialchars($player['login']); ?></b></a>
            <small>Level <?php echo (int)$player['level']; ?> ·
                <img src="/images/power.png" width="14" height="14" alt="Power"> <?php echo $opponentStats['power']; ?>
                <img src="/images/block.png" width="14" height="14" alt="Defense"> <?php echo $opponentStats['block']; ?>
                <img src="/images/health.png" width="14" height="14" alt="Health"> <?php echo $opponentStats['health']; ?>
            </small>
            <?php if ($arena > 0) { ?>
                <form method="post" action="/arena.php" style="display:inline;">
                    <input type="hidden" name="action" value="challenge">
                    <input type="hidden" name="opponent_id" value="<?php echo (int)$player['id']; ?>">
                    <button class="button" type="submit">Challenge (1 battle)</button>
                </form>
            <?php } ?>
        </div>
    <?php }} ?>
</div>

<?php if ($incomingChallenges) { ?>
    <div class="tophead2">
        <div class="title cntr"><span class="color">Incoming Challenges</span></div>
        <?php foreach ($incomingChallenges as $challenge) { ?>
            <div class="tophead">
                <b><?php echo htmlspecialchars($challenge['login']); ?></b> (Level <?php echo (int)$challenge['level']; ?>)
                <small>· <?php echo htmlspecialchars(clock((int)$challenge['created_at'])); ?></small>
                <form method="post" action="/arena.php" style="display:inline;">
                    <input type="hidden" name="challenge_id" value="<?php echo (int)$challenge['id']; ?>">
                    <button class="button" type="submit" name="action" value="accept">Accept &amp; Fight</button>
                    <button class="button button_danger" type="submit" name="action" value="reject">Decline</button>
                </form>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php if ($outgoingChallenges) { ?>
    <div class="tophead2">
        <div class="title cntr"><span class="color">Pending Challenges You Sent</span></div>
        <?php foreach ($outgoingChallenges as $challenge) { ?>
            <div class="tophead">
                Waiting for <b><?php echo htmlspecialchars($challenge['login']); ?></b>
                <small>· Expires after 48 hours · <?php echo htmlspecialchars(clock((int)$challenge['created_at'])); ?></small>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php if ($history) { ?>
    <div class="tophead2">
        <div class="title cntr"><span class="color">Recent Results</span></div>
        <?php foreach ($history as $match) {
            $challengerName = $match['challenger_login'] ?? 'Former player';
            $opponentName = $match['opponent_login'] ?? 'Former player';
            if ($match['status'] === 'resolved') {
                $result = (int)$match['winner_id'] === 0
                    ? 'Draw'
                    : ((int)$match['winner_id'] === $uid ? 'You won' : 'You lost');
            } else {
                $result = ucfirst($match['status']);
            }
        ?>
            <details class="tophead">
                <summary><?php echo htmlspecialchars($result . ': ' . $challengerName . ' vs ' . $opponentName); ?></summary>
                <?php if ($match['battle_log']) { ?><pre><?php echo htmlspecialchars($match['battle_log']); ?></pre><?php } ?>
            </details>
        <?php } ?>
    </div>
<?php } ?>

<?php
foot($uid);
?>
