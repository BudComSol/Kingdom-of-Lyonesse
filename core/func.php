<?php
/**
 * Core Game Functions & System Library
 * Kingdom of Lyonesse MMORPG Engine
 */

require_once __DIR__ . '/../config.php';

// Route segment helper
function is($i) {
    $param = checking($_GET['is'] ?? '');
    $is = explode("/", $param);
    return $is[$i] ?? null;
}

// Text sanitization (PHP 8.1+ null safe)
function checking($text = '') {
    if ($text === null) {
        return '';
    }
    return trim(htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'));
}

// Number sanitization (PHP 8.1+ null safe)
function number($num = 0) {
    if ($num === null) {
        return 0;
    }
    return abs(intval($num));
}

// Database helper: Count rows
function cnt($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

// Database helper: Execute query
function qry($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Database helper: Fetch single row
function fch($sql, $params = array()) {
    global $BaseConnect;
    $stmt = $BaseConnect->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Session Log functions (PHP 8+ compatible)
function logi($log) {
    if ($log !== null && $log !== '') {
        if (!isset($_SESSION['log']) || !is_array($_SESSION['log'])) {
            $_SESSION['log'] = array();
        }
        $_SESSION['log'][] = $log;
    }
}

function logs() {
    if (!isset($_SESSION['log']) || !is_array($_SESSION['log'])) {
        return '';
    }
    $count = count($_SESSION['log']);
    if ($count === 0) {
        return '';
    }
    $text = '';
    foreach ($_SESSION['log'] as $item) {
        $text .= "<div>" . $item . "</div>";
    }
    logn();
    return $text;
}

function logn() {
    unset($_SESSION['log']);
}

function err($err) {
    if ($err !== null && $err !== '') {
        logi("<div class='notify err cntr'><span class='red'>" . $err . "</span></div>");
    }
}

function err2($err2) {
    if ($err2 !== null && $err2 !== '') {
        logi("<div class='notify err cntr'><span class='red'>" . $err2 . "</span></div>");
    }
}

function ruby($ruby) {
    if ($ruby === null || $ruby === false || $ruby === 0) {
        logi("<div class='tophead2 cntr'><span class='red'>Not enough rubies</span> - <a href='/paywk.php'><span class='color'>[buy]</span></a></div>");
    }
}

function msg2($msg2) {
    if ($msg2 !== null && $msg2 !== '') {
        logi("<div class='notify cntr'><span class='lime'>" . $msg2 . "</span></div>");
    }
}

function msg($msg) {
    if ($msg !== null && $msg !== '') {
        logi("<div class='notify cntr'><span class='lime'>" . $msg . "</span></div>");
    }
}

// Pagination offset calculation
function page($num, $str, $result) {
    $num = number($num);
    if ($num < 1) {
        $num = 1;
    }
    $start = $str * $num - $str;
    $endpage = ceil($result / $str);
    if ($num > $endpage) {
        $start = 0;
    }
    return $start;
}

// Pagination navigation bar
function ppage($url, $num, $str, $result) {
    $num = number($num);
    $endpage = ceil($result / $str);

    if ($endpage < 1) {
        $endpage = 1;
    }
    if ($endpage < $num || $num < 1) {
        $num = 1;
    }

    if ($str < $result) {
        echo "<div class='tophead pages'>";

        $n_4 = number($num - 4);
        $n_3 = number($num - 3);
        $n_2 = number($num - 2);
        $n_1 = number($num - 1);
        $n1 = 1;
        $p1 = number($num + 1);
        $p2 = number($num + 2);
        $p3 = number($num + 3);
        $p4 = number($num + 4);

        if ($num != 1) {
            echo "<a class='page' href='" . $url . $n1 . "/' data-page='" . $n1 . "'>&lt;</a> ";
        } else {
            echo "<span class='page-current'>&lt;</span> ";
        }

        if ($num > 4 && $endpage - 1 < $num) {
            echo "<a title='Go to page " . $n_4 . "' class='page' href='" . $url . $n_4 . "/' data-page='" . $n_4 . "'>" . $n_4 . "</a> ";
        }
        if ($num > 3 && $endpage - 2 < $num) {
            echo "<a title='Go to page " . $n_3 . "' class='page' href='" . $url . $n_3 . "/' data-page='" . $n_3 . "'>" . $n_3 . "</a> ";
        }
        if ($num > 2) {
            echo "<a title='Go to page " . $n_2 . "' class='page' href='" . $url . $n_2 . "/' data-page='" . $n_2 . "'>" . $n_2 . "</a> ";
        }
        if ($num > 1) {
            echo "<a title='Go to page " . $n_1 . "' class='page' href='" . $url . $n_1 . "/' data-page='" . $n_1 . "'>" . $n_1 . "</a> ";
        }

        echo " <span class='page-current'>" . $num . "</span> ";

        if ($endpage - 1 > $num) {
            echo "<a class='page' href='" . $url . $p1 . "/' data-page='" . $p1 . "'>" . $p1 . "</a> ";
        }
        if ($endpage - 2 > $num) {
            echo "<a class='page' href='" . $url . $p2 . "/' data-page='" . $p2 . "'>" . $p2 . "</a> ";
        }
        if ($endpage - 3 > $num && $num <= 2) {
            echo "<a class='page' href='" . $url . $p3 . "/' data-page='" . $p3 . "'>" . $p3 . "</a> ";
        }
        if ($endpage - 4 > $num && $num <= 1) {
            echo "<a class='page' href='" . $url . $p4 . "/' data-page='" . $p4 . "'>" . $p4 . "</a> ";
        }

        if ($endpage != $num) {
            if ($endpage - 3 < $num) {
                echo "<a title='Go to page " . $endpage . "' class='page' href='" . $url . $endpage . "/' data-page='" . $endpage . "'>" . $endpage . "</a> ";
            }
            echo "<a class='page' href='" . $url . $endpage . "/' data-page='" . $endpage . "'>&gt;</a> ";
        } else {
            echo "<span class='page-current'>&gt;</span> ";
        }
        echo "</div>";
    }
}

// Text truncation
function text_msg($text, $maxwords = 100, $maxchar = 105) {
    $sep = ' ';
    $words = explode($sep, (string)$text);
    $char = mb_strlen((string)$text, 'UTF-8');
    if (count($words) > $maxwords) {
        $text = implode($sep, array_slice($words, 0, $maxwords));
    }
    if ($char > $maxchar) {
        $text = mb_substr((string)$text, 0, $maxchar, 'UTF-8') . '...';
    }
    return $text;
}

function effectiveCombatStats($player) {
    $stats = [
        'power' => (int)($player['power'] ?? 10),
        'block' => (int)($player['block'] ?? 10),
        'health' => (int)($player['health'] ?? 10),
        'set_pieces' => 0,
        'critical_chance' => 0,
    ];
    $playerId = (int)($player['id'] ?? 0);
    if ($playerId <= 0) {
        return $stats;
    }

    $sets = fch(
        "SELECT `set`, COUNT(*) AS `pieces` FROM `inv` WHERE `id_user` = ? AND `yes` = '1' GROUP BY `set` ORDER BY `pieces` DESC LIMIT 1",
        [$playerId]
    );
    $pieces = (int)($sets['pieces'] ?? 0);
    $stats['set_pieces'] = $pieces;

    if ($pieces >= 8) {
        $stats['power'] = (int)round($stats['power'] * 1.25);
        $stats['health'] = (int)round($stats['health'] * 1.25);
        $stats['critical_chance'] = 10;
    } elseif ($pieces >= 4) {
        $stats['power'] = (int)round($stats['power'] * 1.10);
        $stats['health'] = (int)round($stats['health'] * 1.10);
    }

    return $stats;
}

function clanRewardAmount($userId, $amount) {
    $amount = max(0, (int)$amount);
    if ($amount === 0 || (int)$userId <= 0) {
        return $amount;
    }

    $clan = fch(
        "SELECT c.`level` FROM `users` u INNER JOIN `clans` c ON c.`id` = u.`id_clan` WHERE u.`id` = ? AND u.`id_clan` > 0 LIMIT 1",
        [(int)$userId]
    );
    if (!$clan) {
        return $amount;
    }

    $bonusPercent = min(25, max(0, (int)$clan['level']) * 5);
    return (int)floor($amount * (100 + $bonusPercent) / 100);
}

// Human readable time
function clock($time = null) {
    global $user;
    if ($time === null) {
        $time = time();
    }
    if (isset($user)) {
        $time = $time + 60;
    }

    $timep = date("j M Y \a\\t H:i", $time);
    $time_p[0] = date("j n Y", $time);
    $time_p[1] = date("H:i", $time);

    if ($time_p[0] == date("j n Y")) {
        $timep = "Today at " . date("H:i:s", $time);
    } elseif ($time_p[0] == date("j n Y", time() - 86400)) {
        $timep = "Yesterday at " . $time_p[1];
    }

    return $timep;
}

// Countdown timer formatting
function timer($var) {
    $timer = $var - time();
    if ($timer < 0) {
        $timer = 0;
    }
    $isDay = floor($timer / 86400);
    $isHour = floor($timer / 3600);
    $isMin = floor($timer / 60);
    $hourOs = $isHour - ($isDay * 24);
    $minOs = $isMin - ($isHour * 60);
    $secOs = $timer - ($isMin * 60);

    $day = ($isDay > 0) ? $isDay . " d " : "";
    $hour = ($hourOs > 0) ? $hourOs . " h " : "";
    $minute = ($minOs > 0) ? $minOs . " m " : "";
    $sec = ($secOs > 0) ? $secOs . " s " : "";

    return trim($day . $hour . $minute . $sec);
}

// Month names in English
function month($i) {
    $month = array(
        "1" => "January", "2" => "February", "3" => "March", "4" => "April",
        "5" => "May", "6" => "June", "7" => "July", "8" => "August",
        "9" => "September", "10" => "October", "11" => "November", "12" => "December"
    );
    return $month[$i] ?? '';
}

// Short number formatting (e.g., 1.5k, 2.3m)
function renum($num) {
    $num = (float)$num;
    if ($num >= 1000 && $num < 1000000) {
        $num = round($num / 1000, 2) . "k";
    } elseif ($num >= 1000000 && $num < 1000000000) {
        $num = round($num / 1000000, 2) . "m";
    } elseif ($num >= 1000000000 && $num < 1000000000000) {
        $num = round($num / 1000000000, 2) . "g";
    } elseif ($num >= 1000000000000) {
        $num = round($num / 1000000000000, 2) . "T";
    }
    return (string)$num;
}

// BBCode Parser
function bbcode($text) {
    $str_search = array(
        "/\[(\/?)(br|b|i|u|s)\s*\]/",
        "#\[smile(.+?)\]#is",
        "#\[div class = (.+?)\](.+?)\[\/div\]#is",
        "#\[span class = (.+?)\](.+?)\[\/span\]#is",
        "#///n#is",
        "#\[code\](.+?)\[\/code\]#is",
        "#\[quote\](.+?)\[\/quote\]#is",
        "#\[url=(.+?)\](.+?)\[\/url\]#is",
        "#\[url\](.+?)\[\/url\]#is",
        "#\[img=(.+?)\](.+?)\[\/img\]#is",
        "#\[cntr\](.+?)\[\/cntr\]#is",
        "#\[size=(.+?)\](.+?)\[\/size\]#is",
        "#\[color=(.+?)\](.+?)\[\/color\]#is",
        "#\[list\](.+?)\[\/list\]#is",
        "#\[listn](.+?)\[\/listn\]#is",
        "#\[\*\](.+?)\[\/\*\]#"
    );
    $str_replace = array(
        "<$1$2>",
        "<img src='/images/emoji/\\1.png'>",
        "<div class='\\1'>\\2</div>",
        "<span class='\\1'>\\2</span>",
        "<br/>",
        "<code class='code'>\\1</code>",
        "<table width='95%'><tr><td>Quote</td></tr><tr><td class='quote'>\\1</td></tr></table>",
        "<a href='\\1'>\\2</a>",
        "<a href='\\1'>\\1</a>",
        "<img src='\\1' width='\\2' height='\\2' alt='Image' />",
        "<center>\\1</center>",
        "<span style='font-size:\\1%'>\\2</span>",
        "<font color='\\1'>\\2</font>",
        "<ul>\\1</ul>",
        "<ol>\\1</ol>",
        "<li>\\1</li>"
    );
    return preg_replace($str_search, $str_replace, (string)$text);
}

// ---------------------------------------------------------------------
// Password Security & Verification
// ---------------------------------------------------------------------
function hashPassword($password) {
    return password_hash((string)$password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    if ($password === '' || $hash === '' || $password === null || $hash === null) {
        return false;
    }
    // Modern BCrypt verification
    if (password_verify((string)$password, (string)$hash)) {
        return true;
    }
    // Backward compatibility with legacy md5(md5($password)) and md5($password)
    $doubleMd5 = md5(md5((string)$password));
    $singleMd5 = md5((string)$password);
    if ($hash === $doubleMd5 || $hash === $singleMd5) {
        return true;
    }
    return false;
}

function upgradePasswordIfNeeded($userId, $password, $currentHash) {
    $userId = number($userId);
    if ($userId <= 0) return;
    $doubleMd5 = md5(md5((string)$password));
    $singleMd5 = md5((string)$password);
    if ($currentHash === $doubleMd5 || $currentHash === $singleMd5 || password_needs_rehash((string)$currentHash, PASSWORD_BCRYPT)) {
        $newHash = hashPassword($password);
        qry("UPDATE `users` SET `password` = ? WHERE `id` = ? LIMIT 1", array($newHash, $userId));
    }
}

// ---------------------------------------------------------------------
// Session & Authentication State
// ---------------------------------------------------------------------
$sessionUid = isset($_SESSION['uid']) ? number($_SESSION['uid']) : 0;
$user = false;

if ($sessionUid > 0) {
    $BaseUser = $BaseConnect->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
    $BaseUser->execute([$sessionUid]);
    $user = $BaseUser->fetch(PDO::FETCH_ASSOC);
}

// Remove credentials left by the legacy persistent-login cookie.
if (isset($_COOKIE['uid']) || isset($_COOKIE['password'])) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    foreach (['uid', 'password'] as $cookieName) {
        setcookie($cookieName, '', [
            'expires'  => time() - 86400 * 365,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

$uid = ($user && isset($user['id'])) ? number($user['id']) : 0;

if ($uid === 0 && isset($location) && $location === true) {
    header("Location:/");
    exit();
}

function UserEnter($uid) {
    $uid = number($uid);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = $uid;
}

function UserExit($uid = 0) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('uid', '', [
        'expires'  => time() - 86400 * 365,
        'path'     => '/',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    setcookie('password', '', [
        'expires'  => time() - 86400 * 365,
        'path'     => '/',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function user($users) {
    $userData = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array(number($users)));
    return '<small>' . ($userData['login'] ?? 'Unknown') . '</small>';
}

function user_list2($users) {
    $userData = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array(number($users)));
    if (!$userData) return '<small>Unknown</small>';

    if ($userData['premium_time'] > time()) {
        $inf = '<img src="/images/vip.png" width="15" height="15"> <span class="orange">';
    } else {
        $inf = '<img src="/style/' . $userData['sex'] . '.png" width="16" height="16"> <span class="color">';
    }

    return '<a href="/mail/' . $userData['id'] . '/user">' . $inf . '<small>' . $userData['login'] . '</small></span></a>';
}

function user_list($users) {
    $userData = fch("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array(number($users)));
    if (!$userData) return '<small>Unknown</small>';

    if ($userData['premium_time'] > time()) {
        $inf = '<img src="/images/vip.png" width="15" height="15"> <span class="orange">';
    } else {
        $inf = '<img src="/style/' . $userData['sex'] . '.png" width="16" height="16"> <span class="color">';
    }

    return '<a href="/profile.php?id=' . $userData['id'] . '">' . $inf . '<small>' . $userData['login'] . '</small></span></a>';
}

// Update last active timestamp
if ($uid > 0) {
    qry("UPDATE `users` SET `time_on` = ? WHERE `id` = ? LIMIT 1", array(time(), $uid));
}

// ---------------------------------------------------------------------
// Header Layout
// ---------------------------------------------------------------------
function head($title, $uid) {
    global $user;
    $title = checking($title);
    require_once __DIR__ . "/level.php";
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $uid > 0 ? '' : 'guest-page'; ?>">
<head>
    <meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <meta charset="utf-8">
    <link type="Image/x-icon" href="/style/favicon.ico" rel="icon">
    <meta name="keywords" content="Kingdom, Online Game, Castle Siege, RPG, mobile game, MMORPG">
    <meta name="description" content="Kingdom of Lyonesse Mobile MMORPG. Immerse yourself in a medieval fantasy world and become a legendary knight.">
    <title><?php echo $title ? $title . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="/style/style.css?v=2.2.3" />
    <script defer src="/style/toast.js?v=2.2.0"></script>
    <script defer src="/style/audio.js?v=2.2.0"></script>
    <meta name="author" content="<?php echo APP_NAME; ?>" />
</head>
<body>

<?php
if ($user && isset($user['start']) && $user['start'] >= 4) {
    global $user_level;
    $new_msg = cnt("SELECT (id) FROM `mail` WHERE `id_ank` = ? AND `check` = ? LIMIT 1", array($uid, 1));
    $userLevel = (int)($user['level'] ?? 1);
    $maxExp = isset($user_level[$userLevel]) && $user_level[$userLevel] > 0 ? $user_level[$userLevel] : 100;
    $expPercent = round(($user['exp'] / $maxExp) * 100, 1);

    $arenaTimeLeft = max(0, ($user['arena_time'] ?? 0) - time());
    $forestTimeLeft = max(0, ($user['lec_time'] ?? 0) - time());
?>
<div class="hrs"></div>
<div class="title cntr">
    <span class="color"><?php echo $title; ?></span>
    <?php if (!empty($user['title'])) { ?>
        <br><small><span class="yellow">«<?php echo htmlspecialchars($user['title']); ?>»</span></small>
    <?php } ?>
</div>

<div class="tophead2 cntr">
    <font size="3">
        <span style="float: left;">
            <img class="img1" src="/images/level.png" width="17px" height="17px">
            <span class="lime"><?php echo $userLevel; ?> lvl</span>
        </span>

        <?php $headerStats = effectiveCombatStats($user); ?>
        <img src="/images/power.png" width="17px" height="17px"><?php echo $headerStats['power']; ?>
        <img src="/images/block.png" width="17px" height="17px"><?php echo $headerStats['block']; ?>
        <img src="/images/health.png" width="17px" height="17px"><?php echo $headerStats['health']; ?>

        <span style="float: right;">
        <?php if ($new_msg > 0) { ?>
            <a href="/contacts.php"><img class="img2" src="/images/mail.png" width="17px" height="17px"></a>
        <?php } else { ?>
            <img class="img2" src="/images/exp.png" width="17px" height="17px">
            <span class="orange"><?php echo $expPercent; ?>%</span>
        <?php } ?>
        </span>
    </font>
</div>

<div class="tophead2">
    <div class="progress-bar exp">
        <div style="width:<?php echo $expPercent; ?>%;" class="progress _green"></div>
    </div>
</div>
<br>

<div class="wp200 alc pos-r lptp comp_menu_right">
    <div class="tophead2 cntr" style="padding: 8px 6px;">
        <div class="title cntr"><span class="color" style="font-size: 12px;">⚡ Energy Timers</span></div>
        <div class="hrs"></div>
        <small>
            <b>⚔️ Arena:</b> <span id="arena-timer" class="<?php echo ($arenaTimeLeft > 0) ? 'orange' : 'lime'; ?>"><?php echo ($arenaTimeLeft > 0) ? gmdate("i:s", $arenaTimeLeft) : 'Ready to fight!'; ?></span><br>
            <b>🌲 Forest:</b> <span id="forest-timer" class="<?php echo ($forestTimeLeft > 0) ? 'orange' : 'lime'; ?>"><?php echo ($forestTimeLeft > 0) ? gmdate("i:s", $forestTimeLeft) : 'Ready to hunt!'; ?></span>
        </small>
    </div>

    <div class="sidebar_menu_container">
        <a class="sidebar_nav_item" href="/daily.php">
            <div class="sidebar_nav_icon_wrap"><img src="/images/cup.png" width="22px" height="22px" alt="Daily"></div>
            <span class="sidebar_nav_text yellow">Daily Rewards</span>
        </a>
        <a class="sidebar_nav_item" href="/achievements.php">
            <div class="sidebar_nav_icon_wrap"><img src="/images/records.png" width="22px" height="22px" alt="Achievements"></div>
            <span class="sidebar_nav_text">Achievements</span>
        </a>
        <a class="sidebar_nav_item" href="/clan.php">
            <div class="sidebar_nav_icon_wrap"><img src="/style/clan.png" width="22px" height="22px" alt="Clan"></div>
            <span class="sidebar_nav_text">Clan Castle</span>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let aTime = <?php echo $arenaTimeLeft; ?>;
    let fTime = <?php echo $forestTimeLeft; ?>;

    const aElem = document.getElementById('arena-timer');
    const fElem = document.getElementById('forest-timer');

    function fmt(s) {
        if (s <= 0) return 'Ready!';
        const m = Math.floor(s / 60).toString().padStart(2, '0');
        const sec = (s % 60).toString().padStart(2, '0');
        return `${m}:${sec}`;
    }

    setInterval(() => {
        if (aTime > 0) {
            aTime--;
            if (aElem) {
                aElem.innerText = fmt(aTime);
                if (aTime === 0) { aElem.className = 'lime'; aElem.innerText = 'Ready to fight!'; }
            }
        }
        if (fTime > 0) {
            fTime--;
            if (fElem) {
                fElem.innerText = fmt(fTime);
                if (fTime === 0) { fElem.className = 'lime'; fElem.innerText = 'Ready to hunt!'; }
            }
        }
    }, 1000);
});
</script>

<div class="wp200 alc pos-r lptp comp_menu_left">
    <div class="tophead2 cntr" style="padding: 10px 6px;">
        <a href="/avatar.php" title="Change Avatar">
            <img class="round_image" src="/images/avatar/<?php echo $user['sex']; ?>/<?php echo $user['avatar']; ?>.png" width="95" height="95" alt="Avatar">
        </a>
    </div>

    <div class="tophead2 sidebar_stat_box">
        <?php $sidebarStats = effectiveCombatStats($user); ?>
        <span class="stat_badge" title="Power"><img src="/images/power.png" width="16px" height="16px" alt="Power"> <?php echo $sidebarStats['power']; ?></span>
        <span class="stat_badge" title="Defense"><img src="/images/block.png" width="16px" height="16px" alt="Defense"> <?php echo $sidebarStats['block']; ?></span>
        <span class="stat_badge" title="Health"><img src="/images/health.png" width="16px" height="16px" alt="Health"> <?php echo $sidebarStats['health']; ?></span>
    </div>

    <div class="sidebar_menu_container">
        <a class="sidebar_nav_item" href="/profile.php">
            <div class="sidebar_nav_icon_wrap"><img src="/images/profile.png" width="22px" height="22px" alt="Profile"></div>
            <span class="sidebar_nav_text">Profile</span>
        </a>
        <a class="sidebar_nav_item" href="/item.php">
            <div class="sidebar_nav_icon_wrap"><img src="/images/snar.png" width="22px" height="22px" alt="Equipment"></div>
            <span class="sidebar_nav_text">Equipment</span>
        </a>
        <a class="sidebar_nav_item" href="/skill.php">
            <div class="sidebar_nav_icon_wrap"><img src="/images/ability.png" width="22px" height="22px" alt="Abilities"></div>
            <span class="sidebar_nav_text">Abilities</span>
        </a>
        <a class="sidebar_nav_item" href="/gold.php">
            <div class="sidebar_nav_icon_wrap"><img src="/images/gold.png" width="22px" height="22px" alt="Exchange"></div>
            <span class="sidebar_nav_text">Currency Exchange</span>
        </a>
    </div>

    <div class="sidebar_quick_row">
        <a class="sidebar_quick_btn" href="/contacts.php" title="Mailbox">
            <img src="/images/mail.png" width="18px" height="18px" alt="Mail">
            <?php if ($new_msg > 0) { ?><span class="sidebar_unread_dot"></span><?php } ?>
        </a>
        <div class="sidebar_quick_btn sidebar_quick_lvl" title="Hero Level">
            <span class="yellow"><b><?php echo $userLevel; ?></b> lvl</span>
        </div>
        <a class="sidebar_quick_btn" href="/index.php" title="Town Square">
            <img src="/style/home.png" width="18px" height="18px" alt="Home">
        </a>
    </div>

    <a class="sidebar_nav_item sidebar_ruby_special" href="/paywk.php">
        <div class="sidebar_nav_icon_wrap"><img src="/images/ruby.png" width="22px" height="22px" alt="Rubies"></div>
        <span class="sidebar_nav_text yellow"><b>Buy Rubies</b></span>
    </a>
    <div class="ven"></div>
</div>

<?php
    if (($_SERVER['PHP_SELF'] ?? '') == '/index.php' && ($user['save'] ?? 0) == 1 && ($user['start'] ?? 0) >= 10) {
        echo '<a href="/save.php" class="tophead cntr">Save your game and receive bonus! <br><small><span class="tomato"> <img src="/images/ruby.png" width="16px" height="16px" alt=""> 10 Rubies</span> and <img src="/images/vip.png" width="16px" height="16px" alt=""> <span class="orange">Premium VIP</span></small></a>';
    }

    echo logs();
}
}

// ---------------------------------------------------------------------
// Footer Layout
// ---------------------------------------------------------------------
function foot($uid) {
    global $user;
    $on = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 10800));

    if ($uid > 0) {
        if (($_SERVER['PHP_SELF'] ?? '') == '/index.php' && isset($user['save']) && $user['save'] >= 2 && $user['id'] == 0) {
?>
<div class="tophead2 cntr">
    <span class="orange">Holiday Special Offers</span>
    <br>
    <font color="#888">
        <small>
            100% Bonus when purchasing 100+ rubies<br>
            30% Discount on equipment<br>
            30% Discount on runes<br>
            30% Discount on forging
        </small>
    </font>
</div>
<?php
        }
?>
<div class="tophead2">
    <table style="width:100%;">
        <tr>
            <td style="width: 50%;" class="cntr">
                <a class="tophead" href="/" title="In The Beginning"><img src="/style/homes.png" width="65px" height="59px" alt="In The Beginning"></a>
            </td>
            <td style="width: 50%;" class="cntr">
                <a class="tophead" href="/profile.php" title="You're Game Story"><img src="/style/profils.png" width="65px" height="59px" alt="You're Game Story"></a>
            </td>
        </tr>
    </table>
</div>

<nav class="footer-nav" aria-label="Game navigation">
    <div class="hrs"></div>
    <div class="footer-nav-buttons">
        <a href="/news.php"><?php if (isset($user['news']) && $user['news'] != 0) echo '<span class="lime">'; ?>News<?php if (isset($user['news']) && $user['news'] != 0) echo '</span>'; ?></a>
        <a href="/chat.php"><?php if (isset($user['chat_check']) && $user['chat_check'] != 0) echo '<span class="lime">'; ?>Chat<?php if (isset($user['chat_check']) && $user['chat_check'] != 0) echo '</span>'; ?></a>
        <a href="/online.php">Online: <?php echo $on; ?></a>
        <a href="/index.php?help=1">Game Help</a>
        <a href="/settings.php">Settings</a>
    </div>
</nav>
<div class="hrs"></div>

</body></html>
<?php
    }
}

// ---------------------------------------------------------------------
// Initialize User Quests & Background Data
// ---------------------------------------------------------------------
if ($uid >= 1) {
    $yyy = $BaseConnect->query("SELECT * FROM `quest` ORDER BY `id` ASC");
    while ($post = $yyy->fetch(PDO::FETCH_ASSOC)) {
        $quest = fch("SELECT * FROM `quest_user` WHERE `id_quest` = ? AND `id_user` = ? ORDER BY `id` DESC LIMIT 1", array($post['id'], $uid));
        if (!$quest) {
            qry("INSERT INTO `quest_user` SET `id_user` = ?, `id_quest` = ?, `max_koll` = ?", array($uid, $post['id'], $post['koll']));
        }
    }
}

$dat = array(
    '1' => 'Spam / Advertising',
    '2' => 'Profanity / Toxicity',
    '3' => 'Account Selling / Trading',
    '4' => 'Guild Selling / Trading',
    '5' => 'Exploiting Bugs'
);

$dat2 = array(
    '1' => 'Mute',
    '2' => 'Ban'
);

// Ban Check
$ban = fch("SELECT * FROM `ban` WHERE `id_user` = ? AND `time` > ? ORDER BY `id` DESC LIMIT 1", array($uid, time()));
if ($ban && isset($ban['tipe']) && $ban['tipe'] == 2 && $ban['time'] > time()) {
    head("Account Suspended", $uid);
?>
<div class="tophead cntr">
    <span class="tomato">Your account has been suspended by an administrator.</span>
</div>
<?php
    foot($uid);
    exit();
}

$param_skill = array('', '3', '10', '90', '10', '10', '10', '2', '0', '10', '10', '10', '10', '0', '0', '0');
$time_skill  = array('', '60', '40', '60', '40', '40', '50', '40', '40', '40', '40', '50', '50', '60', '40', '60');
$reset_skill = array('', '0', '5', '0', '5', '5', '0', '0', '4', '3', '5', '5', '4', '0', '4', '4');

if ($uid > 0) {
    $tavern = fch("SELECT * FROM `tavern` WHERE `id_user` = ? LIMIT 1", array($uid));
    $lec = fch("SELECT * FROM `lec_user` WHERE `id_user` = ? LIMIT 1", array($uid));

    if (!$tavern) {
        qry("INSERT INTO `tavern` SET `id_user` = ?", array($uid));
        qry("INSERT INTO `lec_user` SET `id_user` = ?", array($uid));
        header('Location: ?');
        exit();
    }

    $skills = fch("SELECT * FROM `skills` WHERE `id_user` = ? AND `type` = ? LIMIT 1", array($uid, 1));
    if (!$skills) {
        for ($c = 1; $c <= 4; $c++) {
            qry("INSERT INTO `skills_fight` SET `id_user` = ?, `type` = ?", array($uid, $c));
        }
        for ($i = 1; $i <= 15; $i++) {
            $param = $param_skill[$i];
            $time = $time_skill[$i];
            $reset = $reset_skill[$i];
            qry("INSERT INTO `skills` SET `id_user` = ?, `type` = ?, `time_max` = ?, `reset_max` = ?, `param` = ?", array($uid, $i, $time, $reset, $param));
        }
        header('Location: ?');
        exit();
    }
}
