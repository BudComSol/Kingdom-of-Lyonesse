<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['save'] ?? 0) <= 1) {
    header('Location: /save.php');
    exit();
}

$sex = (($user['sex'] ?? '1') == '1') ? 'Male' : 'Female';
$invise = (($user['skills_invise'] ?? '0') == '0') ? 'No' : 'Yes';

if (isset($_GET['sex'])) {
    $gsex = (($user['sex'] ?? '1') == '1') ? '2' : '1';
    qry("UPDATE `users` SET `sex` = ? WHERE `id` = ? LIMIT 1", array($gsex, $uid));
    header('Location: ?');
    exit();
}

if (isset($_GET['invise'])) {
    $ginvise = (($user['skills_invise'] ?? '0') == '0') ? '1' : '0';
    qry("UPDATE `users` SET `skills_invise` = ? WHERE `id` = ? LIMIT 1", array($ginvise, $uid));
    header('Location: ?');
    exit();
}

if (isset($_GET['name'])) {
    $newLogin = checking($_POST['login'] ?? '');
    $err = false;

    if (empty($newLogin) || mb_strlen($newLogin) < 3 || mb_strlen($newLogin) > 20) {
        err2("Hero name must be between 3 and 20 characters");
        $err = true;
    } else {
        $search1 = fch("SELECT * FROM `users` WHERE `login` = ? AND `id` != ? LIMIT 1", array($newLogin, $uid));
        if ($search1) {
            err2("Hero name is already taken");
            $err = true;
        }
    }

    if ($err === false) {
        qry("UPDATE `users` SET `login` = ? WHERE `id` = ? LIMIT 1", array($newLogin, $uid));
        msg2("Hero name updated successfully");
    }
    header('Location: /settings.php');
    exit();
}

if (isset($_GET['password'])) {
    $newPass = checking($_POST['password'] ?? '');
    $err = false;

    if (empty($newPass) || mb_strlen($newPass) < 3 || mb_strlen($newPass) > 32) {
        err2("Password must be between 3 and 32 characters");
        $err = true;
    }

    if ($err === false) {
        $passwordHash = hashPassword($newPass);
        qry("UPDATE `users` SET `password` = ? WHERE `id` = ? LIMIT 1", array($passwordHash, $uid));
        msg("Password updated successfully");
    }
    header('Location: /settings.php');
    exit();
}

head("Settings", $uid);
?>

<div class="tophead cntr">
    <?php echo logs(); ?>
    <form action="?name" method="post">
        New Hero Name:<br>
        <input type="text" name="login" value="<?php echo htmlspecialchars($user['login'] ?? ''); ?>" required minlength="3" maxlength="20"><br>
        <input name="edit" type="submit" value="Change Name">
    </form>
</div>

<div class="tophead cntr">
    <form action="?password" method="post">
        New Password:<br>
        <input type="password" name="password" required minlength="3" maxlength="32"><br>
        <input name="edit" type="submit" value="Change Password">
    </form>
</div>

<div class="tophead">Character Gender: <a href="?sex"><?php echo $sex; ?></a></div>
<div class="tophead">Hide abilities on profile: <a href="?invise"><?php echo $invise; ?></a></div>
<a href="/avatar.php" class="tophead">Change Avatar</a>
<a href="/index.php?exit=1" class="tophead">Log Out</a>

<?php
foot($uid);
?>