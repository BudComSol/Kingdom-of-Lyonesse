<?php
$prem = (($user['premium_time'] ?? 0) > time()) ? 2 : 1;
$combatStats = effectiveCombatStats($user);
$res_rand_win  = intval(rand(90, 150) * $prem);
$res_rand_lose = intval(rand(40, 80) * $prem);

$skill = number($_GET['skill'] ?? 0);
if ($skill >= 1) {
    $active = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $skill));
    if (!$active || ($active['reset'] ?? 0) > time() || ($active['time'] ?? 0) > time()) {
        header('Location: /lec.php');
        exit();
    }

    $kill1  = intval(($user['kill'] ?? 0) * 5);
    $kill2  = intval($active['param'] * $kill1 / 100);
    $kill   = intval($active['param'] + $kill2);

    $medik1 = intval(($user['medik'] ?? 0) * 5);
    $medik2 = intval($active['param'] * $medik1 / 100);
    $medik  = intval($active['param'] + $medik2);

    $mag1   = intval(($user['mag'] ?? 0) * 5);
    $mag2   = intval($active['param'] * $mag1 / 100);
    $mag    = intval($active['param'] + $mag2);

    if ($active['type'] == 1) {
        $atkVal = intval($lec_fight['bot_power'] * $kill / 100);
        qry("UPDATE `lec_fight` SET `bot_hp` = `bot_hp` - ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
    }
    if ($active['type'] == 2) {
        $atkVal = intval($lec_fight['bot_power'] * $kill / 100);
        qry("UPDATE `lec_fight` SET `bot_power` = `bot_power` - ?, `bot_block` = `bot_block` - ?, `s2` = ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $atkVal, $uid));
    }
    if ($active['type'] == 3) {
        $atkVal = rand(max(1, round($lec_fight['power'] / 4)), max(2, round($lec_fight['power'] / 3)));
        qry("UPDATE `lec_fight` SET `bot_hp` = `bot_hp` - ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
    }
    if ($active['type'] == 4) {
        $atkVal = intval($lec_fight['power'] * $kill / 100);
        qry("UPDATE `lec_fight` SET `power` = `power` + ?, `s4` = ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $uid));
    }
    if ($active['type'] == 5) {
        $atkVal = intval($lec_fight['block'] * $kill / 100);
        qry("UPDATE `lec_fight` SET `block` = `block` + ?, `s5` = ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $uid));
    }
    if ($active['type'] == 6) {
        if ($lec_fight['hp'] >= $lec_fight['power']) {
            err2("<div class='b-notice trsmgr'><div class='notify-inner'>Your health is already full!</div></div>");
            header('Location: /lec.php');
            exit();
        }
        $atkVal = intval($lec_fight['power'] * $medik / 100);
        qry("UPDATE `lec_fight` SET `hp` = `hp` + ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
    }
    if ($active['type'] == 7) {
        $atkVal = intval($lec_fight['power'] * $medik / 100);
        qry("UPDATE `lec_fight` SET `hp` = `hp` + ?, `bot_hp` = `bot_hp` - ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $uid));
    }
    if ($active['type'] == 11) {
        $atkVal = intval($lec_fight['bot_power'] * $mag / 100);
        qry("UPDATE `lec_fight` SET `power` = `power` + ?, `bot_power` = `bot_power` - ?, `s11` = ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $atkVal, $atkVal, $uid));
    }
    if ($active['type'] == 13) {
        $atkVal = intval($lec_fight['default2'] * 50 / 100);
        qry("UPDATE `lec_fight` SET `clon_hp` = ? WHERE `id_user` = ? LIMIT 1", array($atkVal, $uid));
    }

    qry("UPDATE `skills` SET `reset` = ?, `time` = ?, `fight` = '1', `fight_time` = ? WHERE `id` = ? LIMIT 1",
        array(time() + $active['reset_max'], time() + $active['time_max'], time() + 5, $active['id']));

    header('Location: /lec.php?ok');
    exit();
}

$active2 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '2' LIMIT 1", array($uid));
if ($active2 && ($active2['reset'] ?? 0) <= time() && ($active2['fight'] ?? 0) == 1 && ($lec_fight['bot_power'] ?? 0) != ($lec_fight['default'] ?? 0)) {
    qry("UPDATE `lec_fight` SET `bot_block` = `bot_block` + ?, `bot_power` = `bot_power` + ? WHERE `id_user` = ? LIMIT 1", array($lec_fight['s2'], $lec_fight['s2'], $uid));
    qry("UPDATE `skills` SET `fight` = '0' WHERE `id` = ? LIMIT 1", array($active2['id']));
    header('Location: /lec.php');
    exit();
}

$active4 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '4' LIMIT 1", array($uid));
if ($active4 && ($active4['reset'] ?? 0) <= time() && ($active4['fight'] ?? 0) == 1 && ($lec_fight['power'] ?? 0) != ($lec_fight['default2'] ?? 0)) {
    qry("UPDATE `lec_fight` SET `power` = `power` - ? WHERE `id_user` = ? LIMIT 1", array($lec_fight['s4'], $uid));
    qry("UPDATE `skills` SET `fight` = '0' WHERE `id` = ? LIMIT 1", array($active4['id']));
    header('Location: /lec.php');
    exit();
}

$active5 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '5' LIMIT 1", array($uid));
if ($active5 && ($active5['reset'] ?? 0) <= time() && ($active5['fight'] ?? 0) == 1 && ($lec_fight['block'] ?? 0) != ($lec_fight['default2'] ?? 0)) {
    qry("UPDATE `lec_fight` SET `block` = `block` - ? WHERE `id_user` = ? LIMIT 1", array($lec_fight['s5'], $uid));
    qry("UPDATE `skills` SET `fight` = '0' WHERE `id` = ? LIMIT 1", array($active5['id']));
    header('Location: /lec.php');
    exit();
}

$active8  = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '8' LIMIT 1", array($uid));
$act8  = ($active8 && ($active8['reset'] ?? 0) > time()) ? 1 : 0;
$active9  = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '9' LIMIT 1", array($uid));
$act9  = ($active9 && ($active9['reset'] ?? 0) > time()) ? 1 : 0;
$active10 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '10' LIMIT 1", array($uid));
$act10 = ($active10 && ($active10['reset'] ?? 0) > time()) ? -1 : 0;
$active14 = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = '14' LIMIT 1", array($uid));
$act14 = ($active14 && ($active14['reset'] ?? 0) > time()) ? 1 : 0;

$power_opp = max(1, rand(round($lec_fight['bot_power'] / 4), round($lec_fight['bot_power'] / 3)));
$block_opp = max(1, rand(round($lec_fight['bot_block'] / 10), round($lec_fight['bot_block'] / 8)));
$power     = max(1, rand(round($lec_fight['power'] / 4), round($lec_fight['power'] / 3)));
$block     = max(1, rand(round($lec_fight['block'] / 10), round($lec_fight['block'] / 8)));

$power_opp = max(0, $power_opp - $block);
$power     = max(0, $power - $block_opp);
$isCrit = rand(1, 100) <= 15 + (int)$combatStats['critical_chance'];
if ($isCrit) {
    $power = (int)($power * 1.5);
}

if (isset($_GET['attack_get'])) {
    if (($lec_fight['fight_time'] ?? 0) > time() && ($lec_fight['bot_hp'] ?? 0) >= 1) {
        err2("<div class='b-notice trsmgr'><div class='notify-inner'>You can only strike once every 2 seconds!</div></div>");
        header('Location: /lec.php');
        exit();
    }

    if ($power >= $lec_fight['bot_hp']) { // Victory
        $resource = "";
        $matType = $lec_fight['type'];

        qry("UPDATE `users` SET `{$matType}` = `{$matType}` + 1, `kills_boss` = `kills_boss` + 1 WHERE `id` = ? LIMIT 1", array($uid));
        qry("UPDATE `lec_user` SET `{$matType}` = `{$matType}` + 1 WHERE `id_user` = ? LIMIT 1", array($uid));
        $resource = "<img src='/images/" . $matType . ".png' width='16' height='16'> +1";

        $rewardExp = clanRewardAmount($uid, intval($res_rand_win / 2));
        $rewardCoins = clanRewardAmount($uid, $res_rand_win);
        qry("UPDATE `users` SET `exp` = `exp` + ?, `silver` = `silver` + ?, `tour` = `tour` + 1 WHERE `id` = ? LIMIT 1", array($rewardExp, $rewardCoins, $uid));
        qry("DELETE FROM `lec_fight` WHERE `id_user` = ?", array($uid));

        msg2("<div class='b-notice trsmgr'><div class='notify-inner'><center>
            <b>Victory! Monster Defeated!</b><br>
            Reward: " . $resource . " &nbsp;|&nbsp;
            <img src='/images/gold.png' width='15px' height='15px'> " . $rewardCoins . " Coins &nbsp;|&nbsp;
            <img src='/images/exp.png' width='15px' height='15px'> " . $rewardExp . " Exp
        </center></div></div>");
        header('Location: /lec.php');
        exit();
    } elseif ($power_opp >= $lec_fight['hp'] && $act8 == 0 && $act9 == 0 && $act14 == 0 && ($lec_fight['clon_hp'] ?? 0) <= 0) { // Defeat
        qry("DELETE FROM `lec_fight` WHERE `id_user` = ?", array($uid));
        $rewardExp = clanRewardAmount($uid, intval($res_rand_lose / 2));
        $rewardCoins = clanRewardAmount($uid, $res_rand_lose);
        qry("UPDATE `users` SET `exp` = `exp` + ?, `silver` = `silver` + ? WHERE `id` = ? LIMIT 1", array($rewardExp, $rewardCoins, $uid));

        err2("<center>
            <b>Defeat!</b><br>
            Consolation: <img src='/images/gold.png' width='15px' height='15px'> " . $rewardCoins . " Coins &nbsp;
            <img src='/images/exp.png' width='15px' height='15px'> " . $rewardExp . " Exp
        </center>");
        header('Location: /lec.php');
        exit();
    }

    if ($act8 == 0 && $act9 == 0 && $act14 == 0) {
        if (($lec_fight['clon_hp'] ?? 0) >= 1) {
            qry("UPDATE `lec_fight` SET `clon_hp` = `clon_hp` - ? WHERE `id_user` = ? LIMIT 1", array($power_opp, $uid));
        } else {
            qry("UPDATE `lec_fight` SET `hp` = `hp` - ? WHERE `id_user` = ? LIMIT 1", array($power_opp, $uid));
        }
    }

    qry("UPDATE `lec_fight` SET `bot_hp` = `bot_hp` - ?, `fight_time` = ? WHERE `id` = ? LIMIT 1", array($power, time() + $act10, $lec_fight['id']));
    header('Location: /lec.php');
    exit();
}

$botHpPercent = round(max(0, $lec_fight['bot_hp']) / max(1, $lec_fight['default']) * 100, 1);
$heroHpPercent = round(max(0, $lec_fight['hp']) / max(1, $combatStats['health']) * 100, 1);
?>

<div class="tophead combat-arena-card" id="monster-card">
    <div class="ohh">
        <img class="imgSkill" src="/images/lec/<?php echo $lec_fight['id_bot']; ?>.png" width="45" height="45" alt="Monster" id="monster-avatar">
    </div>
    <span class="orange" style="font-size: 1.05rem;"><b><?php echo htmlspecialchars($lec_fight['name']); ?></b></span><br>
    <img src="/images/power.png" width="15px" height="15px"> <span id="monster-power"><?php echo $lec_fight['bot_power']; ?></span> &nbsp;
    <img src="/images/block.png" width="15px" height="15px"> <span id="monster-block"><?php echo $lec_fight['bot_block']; ?></span> &nbsp;
    <img src="/images/health.png" width="15px" height="15px"> <span id="monster-hp-text" class="lime"><b><?php echo max(0, $lec_fight['bot_hp']); ?></b> / <?php echo $lec_fight['default']; ?></span>
    <br>
    <div class="hrs"></div>
    <div class="progress-bar exp">
        <div id="monster-hp-bar" style="width:<?php echo $botHpPercent; ?>%; transition: width 0.3s ease;" class="progress _red"></div>
    </div>
</div>

<div class="list2 cntr combat-action-row" style="position: relative;">
    <div id="combat-floating-container" style="position: absolute; top: -20px; left: 0; width: 100%; height: 100%; pointer-events: none; overflow: visible; z-index: 100;"></div>
    <div class="hrs"></div>
    
    <button type="button" id="combat-strike-btn" class="combat-attack-button" title="Strike Monster">
        <img class="imgSkill" src="/images/attack.png" width="48" height="48" alt="Strike">
        <span class="combat-strike-text">STRIKE</span>
    </button>

    <div class="combat-skills-box" style="display: inline-block; vertical-align: middle;">
    <?php
    for ($i = 1; $i < 5; $i++) {
        $fight = fch("SELECT * FROM `skills_fight` WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($uid, $i));
        $info = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($uid, $fight['id_skills'] ?? 0));

        if (!$fight || ($fight['id_skills'] ?? 0) == 0 || !$info) {
            echo '<div class="items-icons imgSkill"></div>';
        } else {
            $color = 'imgSkill';
            $isCooldown = false;

            if (($info['time'] ?? 0) > time()) {
                $color = 'SkillReload';
                $isCooldown = true;
            } elseif (($info['reset'] ?? 0) > time()) {
                $color = 'SkillActive';
                $isCooldown = true;
            }
    ?>
            <button type="button" class="combat-skill-btn <?php echo $color; ?>" data-skill-id="<?php echo $info['id']; ?>" <?php echo $isCooldown ? 'disabled' : ''; ?> style="background: none; border: none; padding: 0; cursor: pointer;">
                <img class="<?php echo $color; ?>" src="/images/skills/<?php echo $info['type']; ?>.png" width="46" height="46" alt="Skill">
            </button>
    <?php
        }
    }
    ?>
    </div>
    <div class="hrs"></div>
</div>

<div class="tophead combat-arena-card" id="hero-card">
    <div class="ohh">
        <img class="imgSkill" src="/images/avatar/<?php echo $user['sex'] ?? 1; ?>/<?php echo $user['avatar'] ?? 1; ?>.png" width="45" height="45" alt="Hero" id="hero-avatar">
    </div>
    <span class="yellow" style="font-size: 1.05rem;"><b><?php echo htmlspecialchars($user['login'] ?? 'Knight'); ?></b></span><br>
    <img src="/images/power.png" width="15px" height="15px"> <span id="hero-power"><?php echo $lec_fight['power']; ?></span> &nbsp;
    <img src="/images/block.png" width="15px" height="15px"> <span id="hero-block"><?php echo $lec_fight['block']; ?></span> &nbsp;
    <img src="/images/health.png" width="15px" height="15px"> <span id="hero-hp-text" class="lime"><b><?php echo max(0, $lec_fight['hp']); ?></b> / <?php echo max(1, $combatStats['health']); ?></span>

    <div class="hrs"></div>
    <div class="progress-bar exp">
        <div id="hero-hp-bar" style="width:<?php echo $heroHpPercent; ?>%; transition: width 0.3s ease;" class="progress _green"></div>
    </div>
</div>

<?php if (($lec_fight['clon_hp'] ?? 0) >= 1) { ?>
<div class="tophead">
    <span class="yellow">Your Shadow Clone:</span> <img src="/images/health.png" width="15px" height="15px"> <?php echo $lec_fight['clon_hp']; ?>
</div>
<?php } ?>

<!-- Dynamic Combat JavaScript Engine -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const strikeBtn = document.getElementById('combat-strike-btn');
    const skillBtns = document.querySelectorAll('.combat-skill-btn');
    const monsterHpBar = document.getElementById('monster-hp-bar');
    const monsterHpText = document.getElementById('monster-hp-text');
    const heroHpBar = document.getElementById('hero-hp-bar');
    const heroHpText = document.getElementById('hero-hp-text');
    const floatingContainer = document.getElementById('combat-floating-container');
    const monsterAvatar = document.getElementById('monster-avatar');
    const heroAvatar = document.getElementById('hero-avatar');

    let isAttacking = false;

    function spawnFloatingNumber(text, color, isMonsterTarget) {
        if (!floatingContainer) return;
        const num = document.createElement('div');
        num.className = 'combat-floating-dmg';
        num.innerHTML = text;
        num.style.color = color;
        num.style.position = 'absolute';
        num.style.left = isMonsterTarget ? '35%' : '65%';
        num.style.top = '10px';
        floatingContainer.appendChild(num);

        setTimeout(() => num.remove(), 1200);
    }

    async function executeCombatAction(action, skillId = 0) {
        if (isAttacking) return;
        isAttacking = true;
        strikeBtn.disabled = true;

        // Visual slash effect
        if (monsterAvatar) {
            monsterAvatar.classList.add('combat-shake');
            setTimeout(() => monsterAvatar.classList.remove('combat-shake'), 400);
        }

        try {
            const url = `/api_combat.php?action=${encodeURIComponent(action)}&skill=${skillId}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                if (window.showToast) window.showToast(data.message || 'Combat action failed', 'error');
                isAttacking = false;
                strikeBtn.disabled = false;
                return;
            }

            // Trigger hit sound
            if (window.gameAudio) window.gameAudio.playClick();

            // Spawn floating damage numbers
            if (data.damage_dealt > 0) {
                const critIcon = data.is_crit ? ' 💥 CRIT!' : '';
                spawnFloatingNumber(`-${data.damage_dealt}${critIcon}`, data.is_crit ? '#f1c40f' : '#ff4757', true);
            }
            if (data.damage_received > 0) {
                setTimeout(() => {
                    spawnFloatingNumber(`-${data.damage_received}`, '#ff6b81', false);
                    if (heroAvatar) {
                        heroAvatar.classList.add('combat-shake');
                        setTimeout(() => heroAvatar.classList.remove('combat-shake'), 400);
                    }
                }, 200);
            }

            // Update Monster HP
            const mPercent = Math.max(0, Math.min(100, (data.monster_hp / data.monster_max_hp) * 100));
            if (monsterHpBar) monsterHpBar.style.width = `${mPercent}%`;
            if (monsterHpText) monsterHpText.innerHTML = `<b>${data.monster_hp}</b> / ${data.monster_max_hp}`;

            // Update Hero HP
            const hPercent = Math.max(0, Math.min(100, (data.hero_hp / data.hero_max_hp) * 100));
            if (heroHpBar) heroHpBar.style.width = `${hPercent}%`;
            if (heroHpText) heroHpText.innerHTML = `<b>${data.hero_hp}</b> / ${data.hero_max_hp}`;

            // Check Status
            if (data.status === 'victory') {
                if (window.showToast) window.showToast('🏆 Victory! Monster Slain!', 'success');
                setTimeout(() => {
                    window.location.href = '/lec.php';
                }, 1000);
                return;
            } else if (data.status === 'defeat') {
                if (window.showToast) window.showToast('💀 Defeat! Retreating to heal...', 'error');
                setTimeout(() => {
                    window.location.href = '/lec.php';
                }, 1000);
                return;
            }

        } catch (err) {
            console.error('Combat AJAX error, falling back:', err);
            window.location.href = '?attack_get';
        } finally {
            setTimeout(() => {
                isAttacking = false;
                strikeBtn.disabled = false;
            }, 600);
        }
    }

    if (strikeBtn) {
        strikeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            executeCombatAction('strike');
        });
    }

    skillBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const sid = btn.getAttribute('data-skill-id');
            if (sid) executeCombatAction('skill', sid);
        });
    });
});
</script>