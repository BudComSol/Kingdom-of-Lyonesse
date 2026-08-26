<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$ava = number($_GET['ava'] ?? 0);
if (isset($_GET['ok']) && $ava >= 1 && $ava <= 7) {
    qry("UPDATE `users` SET `avatar` = ? WHERE `id` = ? LIMIT 1", array($ava, $uid));
    if (isset($user) && is_array($user)) {
        $user['avatar'] = $ava;
    }
    msg("Avatar updated successfully!");
    header('Location: /profile.php');
    exit();
}

head("Change Avatar", $uid);
?>

<div class="tophead">
    <li>Avatar change is completely free!</li>
    <li>Click on any portrait below to set it as your hero avatar.</li>
</div>

<div class="tophead cntr">
    <div class="hrs"></div>
    <?php
    $userSex = (int)($user['sex'] ?? 1);
    if ($userSex < 1 || $userSex > 2) $userSex = 1;
    $currentAvatar = (int)($user['avatar'] ?? 1);

    for ($i = 1; $i <= 7; $i++) {
        $isCurrent = ($currentAvatar === $i);
    ?>
        <div style="display: inline-block; margin: 8px; text-align: center;">
            <a href="?ava=<?php echo $i; ?>&ok" title="<?php echo $isCurrent ? 'Current Avatar' : 'Select Avatar ' . $i; ?>">
                <img class="<?php echo $isCurrent ? 'round_image' : 'imgItem1'; ?>" 
                     src="/images/avatar/<?php echo $userSex; ?>/<?php echo $i; ?>.png" 
                     width="130" height="150" 
                     style="border-radius: 8px; <?php if ($isCurrent) echo 'border: 2px solid #f1c40f; box-shadow: 0 0 15px #f1c40f;'; ?>" 
                     alt="Avatar Option <?php echo $i; ?>">
            </a>
            <br>
            <?php if ($isCurrent) { ?>
                <span class="yellow"><b>[ Active ]</b></span>
            <?php } else { ?>
                <a class="button" style="padding: 4px 12px; font-size: 0.8rem; margin-top: 4px;" href="?ava=<?php echo $i; ?>&ok">Select</a>
            <?php } ?>
        </div>
    <?php
    }
    ?>
    <div class="hrs"></div>
</div>

<a href="/profile.php" class="tophead cntr">← Back to Profile</a>

<?php
foot($uid);
?>