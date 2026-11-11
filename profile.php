<?php
$location = true;
require_once __DIR__ . "/core/func.php";
require_once __DIR__ . "/core/us_level.php";
require_once __DIR__ . "/core/level.php";

$r = mt_rand(1000, 9999);
$id = number($_GET['id'] ?? $uid);
$users = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));

if (!$users) {
    header('Location: /profile.php?id=' . $uid . '&' . $r);
    exit();
}

$title = $users['login'];
$profileCombatStats = effectiveCombatStats($users);
head($title, $uid);

$count_bag  = cnt("SELECT (id) FROM `inv` WHERE `yes` = '0' AND `id_user` = ?", array($uid));
$count_item = cnt("SELECT (id) FROM `inv` WHERE `yes` = '1' AND `id_user` = ?", array($id));

$heroClan = null;
if (($users['id_clan'] ?? 0) > 0) {
    $heroClan = fch("SELECT * FROM `clans` WHERE `id` = ? LIMIT 1", array($users['id_clan']));
}
?>

<div class="tophead cntr">
    <?php if (!empty($users['title'])) { ?>
        <div class="gold" style="font-size: 0.95rem; margin-bottom: 4px;"><b>« <?php echo htmlspecialchars($users['title']); ?> »</b></div>
    <?php } ?>
    <?php if ($heroClan) { ?>
        <div style="font-size: 0.85rem; margin-bottom: 6px;"><a href="/clan.php"><span class="orange">🏰 [<?php echo htmlspecialchars($heroClan['name']); ?>]</span></a></div>
    <?php } ?>
    <div class="hrs"></div>
    <table class="dummy">
        <tr>
            <td class="right blck">
                <div class="items-list">
                <?php
                for ($i = 1; $i < 5; $i++) {
                    $it = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `type` = ? AND `yes` = '1' LIMIT 1", array($users['id'], $i));
                    if (!$it) {
                        echo '<div class="items-icons"></div>';
                    } else {
                        echo '<img class="tophead" src="/images/items/' . $it['set'] . '/' . $it['type'] . '.png" width="45" height="45" alt="Item">';
                    }
                    echo '<br>';
                }
                ?>
                </div>
            </td>

            <td class="cntr">
                <?php if ($users['id'] == $uid) echo '<a href="/avatar.php">'; ?>
                <img class="img3" src="/images/avatar/<?php echo $users['sex']; ?>/<?php echo $users['avatar']; ?>.png" width="70%" height="70%" style="border-radius:8px" alt="Avatar">
                <?php if ($users['id'] == $uid) echo '</a>'; ?>
            </td>

            <td class="left blck">
                <div class="items-list">
                <?php
                for ($i = 5; $i < 9; $i++) {
                    $it = fch("SELECT * FROM `inv` WHERE `id_user` = ? AND `type` = ? AND `yes` = '1' LIMIT 1", array($users['id'], $i));
                    if (!$it) {
                        echo '<div class="items-icons"></div>';
                    } else {
                        echo '<img class="tophead" src="/images/items/' . $it['set'] . '/' . $it['type'] . '.png" width="45" height="45" alt="Item">';
                    }
                    echo '<br>';
                }
                ?>
                </div>
            </td>
        </tr>
    </table>

    <?php if (($users['premium_time'] ?? 0) > time()) { ?>
        <div class="premium"><span class="color">Premium VIP Account</span></div>
    <?php } ?>

    <?php
    if (($users['skills_invise'] ?? 0) == 0) {
        for ($i = 1; $i < 5; $i++) {
            $fight = fch("SELECT * FROM `skills_fight` WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($id, $i));
            $skillId = $fight['id_skills'] ?? 0;
            $inf = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `id` = ? LIMIT 1", array($id, $skillId));

            if (!$fight || $skillId == 0 || !$inf) {
                echo '<div class="items-icons"></div>';
            } else {
                echo '<img class="imgSkill" src="/images/skills/' . $inf['type'] . '.png" width="44" height="44" alt="Ability">';
            }
        }
    }
    ?>
</div>

<div class="tophead">
    <span class="lime">Level: <?php echo $users['level']; ?></span> &nbsp;|&nbsp;
    Daily Streak: <span class="yellow"><b><?php echo (int)($users['daily_streak'] ?? 0); ?> / 7 Days</b></span><br>
    <?php if ($uid == $id) { ?>
        <span class="orange">Exp: <?php echo $users['exp']; ?> / <?php echo $level[$users['level']] ?? 100; ?></span><br>
        <img src="/images/stat.png" width="15px" height="15px" alt=""> <a onclick="{ showSmilies(); return false; }" href="?"><small><span class="color">View Resources</span></small></a>
        <?php require_once __DIR__ . "/templates/statistic.php"; ?>
    <?php } ?>
</div>
<br>

<div class="tophead2">
    <div class="title cntr"><span class="color">Masteries</span></div>
    <table style="width:100%;">
        <tr>
            <td style="width: 33%;" class="cntr">
                <a class="tophead"><img src="/images/t1.png" alt="" width="50" height="50"><br>Assassin<br><?php echo $users['kill']; ?> lvl</a>
            </td>
            <td style="width: 33%;" class="cntr">
                <a class="tophead"><img src="/images/t2.png" alt="" width="50" height="50"><br>Healer<br><?php echo $users['medik']; ?> lvl</a>
            </td>
            <td style="width: 33%;" class="cntr">
                <a class="tophead"><img src="/images/t3.png" alt="" width="50" height="50"><br>Warlock<br><?php echo $users['mag']; ?> lvl</a>
            </td>
        </tr>
    </table>
</div>
<br>

<div class="tophead2">
    <div class="title cntr"><span class="color">Attributes</span></div>
    <table style="width:100%;">
        <tr>
            <td style="width: 33%;" class="cntr">
                <a class="tophead"><img src="/images/power.png" width="17px" height="17px" alt="Power"> <?php echo $profileCombatStats['power']; ?></a>
            </td>
            <td style="width: 33%;" class="cntr">
                <a class="tophead"><img src="/images/block.png" width="17px" height="17px" alt="Defense"> <?php echo $profileCombatStats['block']; ?></a>
            </td>
            <td style="width: 33%;" class="cntr">
                <a class="tophead"><img src="/images/health.png" width="17px" height="17px" alt="Health"> <?php echo $profileCombatStats['health']; ?></a>
            </td>
        </tr>
    </table>
    <img src="/images/stat.png" width="15px" height="15px" alt=""> <a onclick="{ showSmilies2(); return false; }" href="?"><small><span class="color">Attribute Bonuses</span></small></a>
    <?php require_once __DIR__ . "/templates/statistic2.php"; ?>
</div>

<?php if ($uid == $id) { ?>
<div class="tophead2">
    <div class="main_menu_section">
        <a class="main_menu_button" href="/item.php?id=<?php echo $id; ?>">
            <div class="main_menu_button_image active"><img src="/images/snar.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Equipment (<?php echo $count_item; ?>/8)</div>
        </a>
        <a class="main_menu_button" href="/skill.php">
            <div class="main_menu_button_image"><img src="/images/ability.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Abilities</div>
        </a>
        <a class="main_menu_button" href="/bag.php">
            <div class="main_menu_button_image"><img src="/images/bag.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Backpack (<?php echo $count_bag; ?>/30)</div>
        </a>
    </div>

    <div class="main_menu_section">
        <a class="main_menu_button" href="/daily.php">
            <div class="main_menu_button_image active"><img src="/images/cup.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Daily Rewards</div>
        </a>
        <a class="main_menu_button" href="/achievements.php">
            <div class="main_menu_button_image"><img src="/images/records.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Achievements</div>
        </a>
        <a class="main_menu_button" href="/clan.php">
            <div class="main_menu_button_image"><img src="/style/clan.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Clan Castle</div>
        </a>
    </div>

    <div class="main_menu_section">
        <a class="main_menu_button" href="/contacts.php">
            <div class="main_menu_button_image active"><img src="/images/mail.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Mailbox</div>
        </a>
        <a class="main_menu_button" href="/ref.php">
            <div class="main_menu_button_image"><img src="/style/1.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Invite Friends</div>
        </a>
        <a class="main_menu_button" href="/paywk.php">
            <div class="main_menu_button_image"><img src="/images/ruby.png" width="20px" height="20px" alt=""></div>
            <div class="main_menu_button_content">Buy Rubies</div>
        </a>
    </div>
</div>
<?php } else { ?>
<div class="tophead2">
    <div class="main_menu_section">
        <a class="main_menu_button" href="/mail.php?id=<?php echo $id; ?>&user">
            <div class="main_menu_button_image"><img src="/images/mail.png" width="16px" height="16px" alt=""></div>
            <div class="main_menu_button_content">Send Message</div>
        </a>
    </div>
</div>
<?php } ?>

<?php if (($user['adm'] ?? 0) != 0) { ?>
<div class="tophead2">
    <a href="/ban_user.php?id=<?php echo $id; ?>"><span class="orange"><u>Moderate / Ban User</u></span></a><br>
    <a href="/ip_user.php?id=<?php echo $id; ?>"><span class="orange"><u>Multi-accounts</u></span></a><br>
    <a href="/update.php"><span class="orange"><u>Admin Panel</u></span></a>
</div>
<?php } ?>

<?php
foot($uid);
?>