<?php
require_once __DIR__ . '/core/func.php';

$login   = number($_GET['login'] ?? 0);
$password = number($_GET['password'] ?? 0);
$start   = number($_GET['start'] ?? 0);
$ref     = number($_GET['ref'] ?? 0);
$agreee  = number($_GET['agreee'] ?? 0);
$exit    = number($_GET['exit'] ?? 0);
$rules   = number($_GET['rules'] ?? 0);
$help    = number($_GET['help'] ?? 0);
$r       = rand(100000, 900000);

// Logout Action
if ($exit == 1) {
    UserExit($uid);
    header('Location: /');
    exit();
}

// Guest Quick Registration Action
if ($start == 1 && !$uid) {
    $idRef = ($ref >= 1) ? $ref : 0;
    $rou = rand(100000, 999999);
    $rous = (string)rand(100000, 999999);

    $passwordHash = hashPassword($rous);
    $hash = md5(md5((string)$rou));
    $heroLogin = 'Knight_' . $rou;
    $ip = checking($_SERVER['REMOTE_ADDR'] ?? '');

    qry("INSERT INTO `users` SET
        `login` = ?,
        `password` = ?,
        `hash_password` = ?,
        `time_reg` = ?,
        `time_on` = ?,
        `ip` = ?,
        `class` = ?,
        `sex` = ?,
        `start` = ?,
        `ref` = ?",
        array(
            $heroLogin,
            $passwordHash,
            $hash,
            time(),
            time(),
            $ip,
            1,
            1,
            1,
            $idRef
        )
    );

    $uid = (int)$BaseConnect->lastInsertId();
    UserEnter($uid);
    unset($_SESSION['u']);
    header("Location: /?r=" . $r);
    exit();
}

// User Login Action
if ($login == 1 && isset($_GET['enter'])) {
    $loginInput = checking($_POST['login'] ?? '');
    $passInput  = checking($_POST['password'] ?? '');

    $quser = fch("SELECT * FROM `users` WHERE `login` = ? LIMIT 1", array($loginInput));

    if ($quser && verifyPassword($passInput, $quser['password'])) {
        upgradePasswordIfNeeded($quser['id'], $passInput, $quser['password']);
        UserEnter($quser['id']);
        header("Location: /?r=" . $r);
        exit();
    } else {
        err("Invalid hero name or password");
        header("Location: /?login=1&r=" . $r);
        exit();
    }
}

// Password Reset Confirmation Action
if (isset($_GET['id']) && isset($_GET['hash']) && isset($_GET['enter'])) {
    $id = number($_GET['id']);
    $hash = checking($_GET['hash']);
    $user_count = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));

    if (!$user_count || $user_count['hash_password'] != $hash) {
        header('Location: ?r=' . $r);
        exit();
    }

    $newPassword = checking($_POST['password'] ?? '');
    if (empty($newPassword)) {
        err("Password field cannot be empty!");
        header('Location: ?id=' . $id . '&hash=' . $hash . '&r=' . $r);
        exit();
    } elseif (mb_strlen($newPassword) < 3 || mb_strlen($newPassword) > 32) {
        err("Password must be between 3 and 32 characters!");
        header('Location: ?id=' . $id . '&hash=' . $hash . '&r=' . $r);
        exit();
    } else {
        $rou = rand(100000, 999999);
        $newHash = md5(md5((string)$rou));
        $newPasswordHash = hashPassword($newPassword);

        qry("UPDATE `users` SET `password` = ?, `hash_password` = ? WHERE `id` = ? LIMIT 1", array($newPasswordHash, $newHash, $id));
        UserEnter($id);
        msg("Password updated successfully!");
        header('Location: /');
        exit();
    }
}

// Password Recovery Request Action
if ($password == 1 && isset($_GET['enter'])) {
    $loginInput = checking($_POST['login'] ?? '');
    $login_count = fch("SELECT * FROM `users` WHERE `login` = ? LIMIT 1", array($loginInput));

    if (!$login_count) {
        err("Hero not found");
    } elseif (empty($login_count['email'])) {
        err("Hero is not linked to an email address");
    } else {
        $rou = rand(100000, 999999);
        qry("UPDATE `users` SET `hash_password` = ? WHERE `login` = ? LIMIT 1", array(md5((string)$rou), $loginInput));

        $msg = "Password recovery for Kingdom of Lyonesse.\n\nHero: " . $loginInput . "\nClick the link to reset your password:\n" . BASE_URL . "/?id=" . $login_count['id'] . "&hash=" . md5((string)$rou) . "\n\nIf you did not request this, please ignore this email.\n--\nKingdom of Lyonesse Administration";
        $subject = 'Password Recovery - Kingdom of Lyonesse';
        $fromHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        @mail($login_count['email'], $subject, $msg, "From: admin@" . $fromHost);
        msg('Password reset link has been sent to your email');
        header('Location: ?password=1&r=' . $r);
        exit();
    }
    header('Location: ?password=1&r=' . $r);
    exit();
}

// ---------------------------------------------------------------------
// VIEW RENDERING
// ---------------------------------------------------------------------

if ($agreee == 1) {
    head("Terms & User Agreement", $uid);
    require_once __DIR__ . '/about/agreee.php';
    foot($uid);
    exit();
}

if (!$uid) {
    head("Kingdom of Lyonesse", 0);
?>
<div class="landing-content">
    <div class="dragon-sky" aria-hidden="true">
        <div class="dragon-container">
            <svg class="dragon-svg" viewBox="0 0 360 200" xmlns="http://www.w3.org/2000/svg" focusable="false">
                <path class="dragon-wing-back" d="M185,105 C165,65 135,35 95,20 C110,45 108,60 100,72 C115,65 128,70 132,85 C145,78 158,82 162,96 C172,92 180,98 185,105 Z" />
                <path class="dragon-body" d="
                    M12,135 L28,128 L24,136 L45,134
                    Q85,138 125,122
                    L132,115 L136,120 L144,112 L148,118 L156,110 L160,115
                    Q185,105 215,108 Q245,112 268,94 Q282,82 296,80
                    L282,66 L298,74 L288,58 L306,74
                    C318,74 330,78 342,84
                    L334,90 L340,94 L322,96
                    Q305,96 292,106 Q275,122 252,126
                    L246,144 L254,148 L242,148 L238,132
                    Q205,136 172,132
                    L160,152 L170,158 L154,156 L146,162 L150,148 L156,132
                    Q110,144 65,142 Q35,142 12,135 Z" />
                <path class="dragon-wing-front" d="M195,112 C185,60 148,22 98,5 C118,32 115,52 104,68 C122,58 138,65 144,82 C160,72 175,78 180,95 C188,92 192,102 195,112 Z" />
            </svg>
        </div>
    </div>
    <div class="logo">
        <picture>
            <source type="image/webp" media="(max-resolution: 1dppx)" srcset="/images/logo-272.webp">
            <img src="/images/logo.png" width="320" height="213" alt="Logo" fetchpriority="high">
        </picture>
    </div>    
    <a class="enter_button" href="/?start=1">
        <img src="/images/button_green.png" width="335" height="88" alt="Battle Button">
    </a>
    <div class="title cntr">
       <span>Got An Account - <a href="/?login=1" id="landing-login-button" class="landing-login-button">Login!</a></span>
    </div>
</div>
<center>

<?php
if ($login == 1) {
    echo logs();
?>
<div class="title cntr"><span class="color">Hero Login</span></div>
<div class="hrs"></div>
<form class="login-form" action="?login=1&enter&r=<?php echo $r; ?>" method="post">
    Hero Name:<br />
    <input type="text" name="login" maxlength="20" value="" required placeholder="Enter hero name..." /><br /><br />
    Password:<br />
    <input type="password" name="password" maxlength="32" value="" required placeholder="Enter password..." /><br /><br />
    <input class="title cntr" type="submit" value="Login" /><br /><br />
    <a class="a" href="/?password=1"><u>Forgot Password?</u></a><br />
    <a class="a" href="/?start=1"><u>New Hero Registration</u></a>
</form>
<?php
    foot(0);
    exit();
}

if (isset($_GET['id']) && isset($_GET['hash'])) {
    $id = number($_GET['id']);
    $hash = checking($_GET['hash']);
?>
<div class="tophead cntr">
    <div class="title cntr"><span class="color">Set New Password</span></div>
    <div class="hrs"></div>
    <form action="?id=<?php echo $id; ?>&hash=<?php echo $hash; ?>&enter&r=<?php echo $r; ?>" method="post">
        New Password:<br><input type="password" name="password" required minlength="3" maxlength="32"><br><br>
        <input type="submit" value="Save Password">
    </form>
</div>
<?php
    foot(0);
    exit();
}

if ($password == 1) {
?>
<div class="tophead cntr"><span class="orange"><font size="3"><b>Password Recovery</b></font></span></div>
<div class="tophead cntr">
<form action="?password=1&enter&r=<?php echo $r; ?>" method="post">
    Hero Name:<br><input type="text" name="login" required placeholder="Enter your hero name..."><br><br>
    <input name="add" type="submit" value="Send Recovery Link">
    <div class="password-recovery-links">
        <a href="/?login=1"><u>Already have a hero? Log In</u></a>
        <a href="/?start=1"><u>No hero yet? Start Game</u></a>
        <a href="/?agreee=1"><u>Terms & Agreement</u></a>
    </div>
</form>
</div>
<?php
    foot(0);
    exit();
}
?>
</center>
<?php
    foot(0);
    exit();
} else {
    if ($rules == 1) {
        head("Game Rules", $uid);
        require_once __DIR__ . '/about/rules.php';
        foot($uid);
        exit();
    }

    if ($help == 1) {
        head("Game Guide & Help", $uid);
        require_once __DIR__ . '/about/help.php';
        foot($uid);
        exit();
    }

    $tutorialStep = (int)($user['start'] ?? 1);
    if ($tutorialStep <= 9 && $tutorialStep != 7 && number($_GET['go'] ?? 0) == 1) {
        qry("UPDATE `users` SET `start` = `start` + ? WHERE `id` = ? LIMIT 1", array(1, $uid));
        header('Location: /');
        exit();
    }

    if ($tutorialStep == 7 && isset($_GET['sex'])) {
        $gsex = (($user['sex'] ?? '1') == '1') ? '2' : '1';
        qry("UPDATE `users` SET `sex` = ? WHERE `id` = ? LIMIT 1", array($gsex, $uid));
        header('Location: /');
        exit();
    }

    if ($tutorialStep == 7 && isset($_GET['form'])) {
        $loginInput = checking($_POST['login'] ?? '');
        $err = false;

        if (mb_strlen($loginInput) < 3 || mb_strlen($loginInput) > 20) {
            err2("Hero name must be between 3 and 20 characters");
            $err = true;
        } else {
            $search1 = fch("SELECT * FROM `users` WHERE `login` = ? AND `id` != ? LIMIT 1", array($loginInput, $uid));
            if ($search1) {
                err2("Hero name is already taken");
                $err = true;
            }
        }

        if ($err === false) {
            qry("UPDATE `users` SET `login` = ?, `save` = ?, `start` = `start` + ? WHERE `id` = ? LIMIT 1", array($loginInput, 1, 1, $uid));
        }

        header('Location: /');
        exit();
    }

    head("Kingdom of Lyonesse", $uid);

    // Step 1: Tutorial onboarding
    if (($user['start'] ?? 1) <= 3) {
        require_once __DIR__ . '/templates/start.php';
    } elseif (($user['start'] ?? 1) >= 4 && ($user['start'] ?? 1) <= 9) { // Step 2: Advanced tutorial
        require_once __DIR__ . '/templates/start2.php';
        foot($uid);
    } else { // Main Game Hub
        require_once __DIR__ . '/templates/index.php';
        foot($uid);
    }
}
?>
