<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['save'] ?? 0) >= 2) {
    header('Location: /');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['form'])) {
    if (!empty($_POST)) {
        $password = checking($_POST['password'] ?? '');
        $email    = checking($_POST['email'] ?? '');
        $err      = false;

        if (mb_strlen($password) < 3 || mb_strlen($password) > 32) {
            err("Password must be between 3 and 32 characters");
            $err = true;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            err("Please enter a valid email address");
            $err = true;
        } else {
            $search4 = fch("SELECT * FROM `users` WHERE `email` = ? AND `id` != ? LIMIT 1", array($email, $uid));
            if ($search4) {
                err("This email address is already registered to another hero");
                $err = true;
            }
        }

        if ($err === false) {
            $passwordHash = hashPassword($password);
            $rou = rand(100000, 999999);
            $hash = md5(md5((string)$rou));

            // Grant 10 rubies bonus and 2 hours VIP
            qry("UPDATE `users` SET `password` = ?, `hash_password` = ?, `email` = ?, `save` = 2, `gold` = `gold` + 10, `premium` = 2, `premium_time` = ? WHERE `id` = ? LIMIT 1", 
                array($passwordHash, $hash, $email, time() + 7200, $uid));

            UserEnter($uid, $password);
            unset($_SESSION['u']);
            msg("Hero account saved successfully! Received +10 Rubies and VIP bonus.");
            header("Location: /");
            exit();
        }

        header("Location: /save.php");
        exit();
    }
}

head("Save Hero Account", $uid);
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Link & Protect Hero Account</span></div>
    <small><font color="#a4b0be">Save your progress, get an instant <b>+10 Rubies bonus</b> and <b>VIP status</b>!</font></small>
    <p>Saving creates an account you can come back and use anytime.</p>
    <p>Not saving here means should you return it will be as a GUEST.</p>
    <div class="hrs"></div>

    <form method="post" action="?form">
        Password:<br>
        <input type="password" name="password" required minlength="3" maxlength="32" placeholder="Choose a password">
        <br><br>
        E-mail:<br>
        <input type="email" name="email" required placeholder="your.email@domain.com">
        <br>
        <div class="hrs"></div>
        <input type="submit" value="Save & Claim Bonus">
    </form>
    <a href="/index.php?exit=1" class="button">No Thanks - Log Me Out</a>
    <br><small>Without saving, you may not be able to recover this hero.</small>
</div>

<?php
foot($uid);
?>
