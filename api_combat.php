<?php
require_once __DIR__ . '/core/func.php';

header('Content-Type: application/json; charset=utf-8');

if (!$uid) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$lec_fight = fch("SELECT * FROM `lec_fight` WHERE `id_user` = ? LIMIT 1", array($uid));
if (!$lec_fight) {
    echo json_encode(['success' => false, 'status' => 'no_fight', 'message' => 'No active battle found']);
    exit();
}

$action = checking($_REQUEST['action'] ?? 'strike');
$skillId = number($_REQUEST['skill'] ?? 0);

$prem = (($user['premium_time'] ?? 0) > time()) ? 2 : 1;
$res_rand_win  = intval(rand(90, 150) * $prem);
$res_rand_lose = intval(rand(40, 80) * $prem);

// Active skill effects check
$active8  = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '8' LIMIT 1", array($uid));
$act8  = ($active8 && ($active8['reset'] ?? 0) > time()) ? 1 : 0;
$active9  = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '9' LIMIT 1", array($uid));
$act9  = ($active9 && ($active9['reset'] ?? 0) > time()) ? 1 : 0;
$active10 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '10' LIMIT 1", array($uid));
$act10 = ($active10 && ($active10['reset'] ?? 0) > time()) ? -1 : 0;
$active14 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '14' LIMIT 1", array($uid));
$act14 = ($active14 && ($active14['reset'] ?? 0) > time()) ? 1 : 0;

// Execute Skill Action if requested
if ($action === 'skill' && $skillId >= 1) {
    $active = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $skillId));
    if (!$active || ($active['reset'] ?? 0) > time()) {
        echo json_encode(['success' => false, 'message' => 'Skill is currently on cooldown']);
        exit();
    }

    $kill1  = intval(($user['kill'] ?? 0) * 5);
    $kill   = intval($active['param'] + ($active['param'] * $kill1 / 100));

    $medik1 = intval(($user['medik'] ?? 0) * 5);
    $medik  = intval($active['param'] + ($active['param'] * $medik1 / 100));

    $mag1   = intval(($user['mag'] ?? 0) * 5);
    $mag    = intval($active['param'] + ($active['param'] * $mag1 / 100));

    $skillLog = "Activated ability: " . htmlspecialchars($active['name'] ?? 'Skill') . "!";

    if ($active['type'] == 1) {
        $atkVal = intval($lec_fight['bot_power'] * $kill / 100);
        qry("UPDATE `lec_fight` SET `bot_hp` = `bot_hp` - ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
        $skillLog .= " Dealt {$atkVal} bonus damage!";
    }
    if ($active['type'] == 2) {
        $atkVal = intval($lec_fight['bot_power'] * $kill / 100);
        qry("UPDATE `lec_fight` SET `bot_power` = `bot_power` - ?, `bot_block` = `bot_block` - ?, `s2` = ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $atkVal, $uid));
        $skillLog .= " Weakened monster power and defense by {$atkVal}!";
    }
    if ($active['type'] == 3) {
        $atkVal = rand(max(1, round($lec_fight['power'] / 4)), max(2, round($lec_fight['power'] / 3)));
        qry("UPDATE `lec_fight` SET `bot_hp` = `bot_hp` - ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
        $skillLog .= " Smote enemy for {$atkVal} damage!";
    }
    if ($active['type'] == 6) {
        $atkVal = intval($lec_fight['power'] * $medik / 100);
        qry("UPDATE `lec_fight` SET `hp` = `hp` + ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
        $skillLog .= " Restored +{$atkVal} Health!";
    }
    if ($active['type'] == 7) {
        $atkVal = intval($lec_fight['power'] * $medik / 100);
        qry("UPDATE `lec_fight` SET `hp` = `hp` + ?, `bot_hp` = `bot_hp` - ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $uid));
        $skillLog .= " Drained {$atkVal} life from monster!";
    }

    qry("UPDATE `skills` SET `reset` = ?, `time` = ?, `fight` = '1', `fight_time` = ? WHERE `id` = ? LIMIT 1",
        array(time() + $active['reset_max'], time() + $active['time_max'], time() + 5, $active['id']));

    // Refresh state
    $lec_fight = fch("SELECT * FROM `lec_fight` WHERE `id_user` = ? LIMIT 1", array($uid));
}

// Compute normal turn damage
$power_opp = max(1, rand(round($lec_fight['bot_power'] / 4), round($lec_fight['bot_power'] / 3)));
$block_opp = max(1, rand(round($lec_fight['bot_block'] / 10), round($lec_fight['bot_block'] / 8)));
$power     = max(1, rand(round($lec_fight['power'] / 4), round($lec_fight['power'] / 3)));
$block     = max(1, rand(round($lec_fight['block'] / 10), round($lec_fight['block'] / 8)));

$power_opp = max(0, $power_opp - $block);
$power     = max(0, $power - $block_opp);

$combatStats = effectiveCombatStats($user);
$criticalChance = 15 + (int)$combatStats['critical_chance'];
$isCrit = false;
if (rand(1, 100) <= $criticalChance) {
    $power = intval($power * 1.5);
    $isCrit = true;
}

// Victory Check
if ($power >= $lec_fight['bot_hp']) {
    $matType = $lec_fight['type'];
    try {
        $BaseConnect->beginTransaction();
        qry("UPDATE `users` SET `{$matType}` = `{$matType}` + 1, `kills_boss` = `kills_boss` + 1 WHERE `id` = ? LIMIT 1", array($uid));
        qry("UPDATE `lec_user` SET `{$matType}` = `{$matType}` + 1 WHERE `id_user` = ? LIMIT 1", array($uid));
        $rewardExp = clanRewardAmount($uid, intval($res_rand_win / 2));
        $rewardCoins = clanRewardAmount($uid, $res_rand_win);
        qry("UPDATE `users` SET `exp` = `exp` + ?, `silver` = `silver` + ?, `tour` = `tour` + 1 WHERE `id` = ? LIMIT 1", array($rewardExp, $rewardCoins, $uid));
        qry("DELETE FROM `lec_fight` WHERE `id_user` = ?", array($uid));

        $droppedItem = null;
        if (rand(1, 100) <= 35) {
            $randomItem = fch("SELECT * FROM `items` WHERE `level` <= ? ORDER BY RAND() LIMIT 1", array((int)($user['level'] ?? 1)));
            if ($randomItem) {
                qry("INSERT INTO `inv` (`id_user`, `set`, `name`, `type`, `level`, `power`, `block`, `health`, `energy`, `max_smitch`, `smitch`, `yes`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, 0)",
                    array($uid, $randomItem['set'], $randomItem['name'], $randomItem['type'], $randomItem['level'], $randomItem['power'], $randomItem['block'], $randomItem['health'], $randomItem['max_smitch']));
                $droppedItem = [
                    'name' => $randomItem['name'],
                    'quality' => (int)$randomItem['set'],
                    'icon' => "/images/items/" . $randomItem['set'] . "/" . $randomItem['type'] . ".png"
                ];
            }
        }
        $BaseConnect->commit();
    } catch (Throwable $e) {
        if ($BaseConnect->inTransaction()) {
            $BaseConnect->rollBack();
        }
        error_log("Combat victory failed: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not complete combat. Please try again.']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'status' => 'victory',
        'hero_hp' => max(0, $lec_fight['hp']),
        'hero_max_hp' => max(1, $combatStats['health']),
        'monster_hp' => 0,
        'monster_max_hp' => max(1, $lec_fight['default']),
        'damage_dealt' => $power,
        'damage_received' => 0,
        'is_crit' => $isCrit,
        'reward' => [
            'gold' => $rewardCoins,
            'exp' => $rewardExp,
            'resource' => $matType,
            'item' => $droppedItem
        ],
        'message' => "Victory! Monster slain!"
    ]);
    exit();
}

// Defeat Check
if ($power_opp >= $lec_fight['hp'] && $act8 == 0 && $act9 == 0 && $act14 == 0 && ($lec_fight['clon_hp'] ?? 0) <= 0) {
    qry("DELETE FROM `lec_fight` WHERE `id_user` = ?", array($uid));
    $rewardExp = clanRewardAmount($uid, intval($res_rand_lose / 2));
    $rewardCoins = clanRewardAmount($uid, $res_rand_lose);
    qry("UPDATE `users` SET `exp` = `exp` + ?, `silver` = `silver` + ? WHERE `id` = ? LIMIT 1", array($rewardExp, $rewardCoins, $uid));

    echo json_encode([
        'success' => true,
        'status' => 'defeat',
        'hero_hp' => 0,
        'hero_max_hp' => max(1, $combatStats['health']),
        'monster_hp' => max(0, $lec_fight['bot_hp']),
        'monster_max_hp' => max(1, $lec_fight['default']),
        'damage_dealt' => $power,
        'damage_received' => $power_opp,
        'is_crit' => $isCrit,
        'reward' => [
            'gold' => $rewardCoins,
            'exp' => $rewardExp
        ],
        'message' => "Defeat! Fall back to heal!"
    ]);
    exit();
}

// Apply ongoing turn damage
if ($act8 == 0 && $act9 == 0 && $act14 == 0) {
    if (($lec_fight['clon_hp'] ?? 0) >= 1) {
        qry("UPDATE `lec_fight` SET `clon_hp` = `clon_hp` - ? WHERE `id_user` = ? LIMIT 1", array($power_opp, $uid));
    } else {
        qry("UPDATE `lec_fight` SET `hp` = `hp` - ? WHERE `id_user` = ? LIMIT 1", array($power_opp, $uid));
    }
}

qry("UPDATE `lec_fight` SET `bot_hp` = `bot_hp` - ?, `fight_time` = ? WHERE `id` = ? LIMIT 1", array($power, time() + $act10, $lec_fight['id']));

$updatedFight = fch("SELECT * FROM `lec_fight` WHERE `id_user` = ? LIMIT 1", array($uid));

echo json_encode([
    'success' => true,
    'status' => 'in_progress',
    'hero_hp' => max(0, (int)($updatedFight['hp'] ?? 0)),
    'hero_max_hp' => max(1, $combatStats['health']),
    'monster_hp' => max(0, (int)($updatedFight['bot_hp'] ?? 0)),
    'monster_max_hp' => max(1, $lec_fight['default']),
    'damage_dealt' => $power,
    'damage_received' => $power_opp,
    'is_crit' => $isCrit,
    'message' => "Turn completed."
]);
