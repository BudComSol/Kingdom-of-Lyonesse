<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$search = number($_GET['search'] ?? 0);

if (isset($_POST['search_player'])) {
    $login = checking($_POST['login'] ?? '');
    if (!empty($login)) {
        $count_user = fch("SELECT * FROM `users` WHERE `login` = ? LIMIT 1", array($login));
        if ($count_user) {
            header("Location: /profile.php?id=" . $count_user['id']);
            exit();
        } else {
            err("Hero not found!");
        }
    }
}

head("Online Heroes", $uid);

$count = cnt("SELECT (id) FROM `users` WHERE `time_on` > ?", array(time() - 10800));

if ($search == 2) {
?>
    <div class="tophead cntr">
        Hero Name:<br>
        <form action="?search=2" method="post">
            <input type="text" name="login" maxlength="50" required placeholder="Enter hero name..." /><br><br>
            <input type="submit" name="search_player" value="Search Hero">
        </form>
    </div>
    <a href="/online.php" class="tophead cntr">← Back to Online List</a>
<?php
    foot($uid);
    exit();
}

$str = 10;
$url = "/online.php?";
$num = abs(intval($_GET['p'] ?? 1));
if ($num < 1) $num = 1;
$result = $count;

$start = page($num, $str, $result);
$user_q = $BaseConnect->prepare("SELECT * FROM `users` WHERE `time_on` > :t ORDER BY `level` DESC LIMIT :start, :limit");
$user_q->bindValue(':t', time() - 10800, PDO::PARAM_INT);
$user_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
$user_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
$user_q->execute();

$i = 1;
$now = time();
while ($users = $user_q->fetch(PDO::FETCH_ASSOC)) {
    $class = ($i % 2 == 0) ? 'list2' : 'list';
    $sessionStarted = (int)$users['session_started'];
    $registered = (int)$users['time_reg'];
    // NPCs are created with localhost IP and activity set five years ahead.
    $activityOffset = (int)$users['time_on'] - $registered;
    $isNpc = $users['ip'] === '127.0.0.1' && $registered > 0
        && $activityOffset >= 86400 * 365 * 5 && $activityOffset < 86400 * 365 * 5 + 60;
    echo '<div class="' . $class . '">';
    echo '<table style="width:100%;"><tr>';
    echo '<td style="width:70%; text-align:left;">' . user_list($users['id']) . '</td>';
    echo '<td style="width:30%; text-align:right;"><small>';
    if ($isNpc || $sessionStarted > 0) {
        $sessionSeconds = max(0, $now - ($isNpc ? $registered : $sessionStarted));
        $sessionMinutes = intdiv($sessionSeconds, 60);
        echo ($isNpc ? 'Uptime' : 'Since') . ': <span class="session-duration" data-seconds="' . $sessionSeconds . '">'
            . intdiv($sessionMinutes, 60) . 'h ' . ($sessionMinutes % 60) . 'm</span>';
    } else {
        echo 'Since: unavailable';
    }
    echo '</small></td>';
    echo '</tr></table>';
    echo '</div>';
    $i++;
}

ppage($url, $num, $str, $result);
?>
<script>
(() => {
    const started = performance.now();
    const durations = document.querySelectorAll('.session-duration');
    function updateDurations() {
        const elapsed = Math.floor((performance.now() - started) / 1000);
        durations.forEach((element) => {
            const minutes = Math.floor((Number(element.dataset.seconds) + elapsed) / 60);
            element.textContent = Math.floor(minutes / 60) + 'h ' + (minutes % 60) + 'm';
        });
    }
    if (durations.length) setInterval(updateDurations, 1000);
})();
</script>
<a href="?search=2" class="tophead cntr">🔍 Search for Hero</a>
<?php
foot($uid);
?>