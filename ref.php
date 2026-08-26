<?php
$location = true;
require_once __DIR__ . "/core/func.php";

head("Invite Friends & Referrals", $uid);

if (isset($_GET['list'])) {
    $count = cnt("SELECT (id) FROM `users` WHERE `ref` = ?", array($uid));
    $str = 10;
    $url = "/ref.php?list=1&";
    $num = abs(intval($_GET['p'] ?? 1));
    if ($num < 1) $num = 1;
    $result = $count;

    $start = page($num, $str, $result);
    $user_q = $BaseConnect->prepare("SELECT * FROM `users` WHERE `ref` = :uid ORDER BY `id` DESC LIMIT :start, :limit");
    $user_q->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
    $user_q->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $user_q->bindValue(':limit', (int)$str, PDO::PARAM_INT);
    $user_q->execute();

    if ($count == 0) {
        echo '<div class="list cntr">No invited friends yet.</div>';
    }

    $i = 1;
    while ($users = $user_q->fetch(PDO::FETCH_ASSOC)) {
        $class = ($i % 2 == 0) ? 'list2' : 'list';
        echo '<div class="' . $class . '">';
        echo user_list($users['id']);
        echo '</div>';
        $i++;
    }

    ppage($url, $num, $str, $result);
?>
    <a href="/ref.php" class="tophead">Back to Referral Program</a>
<?php
    foot($uid);
    exit();
}

$refUrl = BASE_URL . "/?start=1&ref=" . $user['id'];
$count_ref = cnt("SELECT (id) FROM `users` WHERE `ref` = ?", array($uid));
?>

<div class="tophead cntr">
    Your Personal Referral Link:<br><br>
    <input type="text" style="width: 90%; text-align: center;" value="<?php echo htmlspecialchars($refUrl); ?>" readonly onclick="this.select();" />
    <br><br>
    <small>For every ruby purchase your invited friends make, you will receive a <b>25% Ruby bonus</b> directly into your treasury!</small>
</div>

<div class="tophead">
    <img src="/images/ruby.png" width="17px" height="17px" alt="Rubies"> Total Rubies Earned: <?php echo $user['ref_ruby'] ?? 0; ?><br>
    <img src="/images/style/1.png" width="17px" height="17px" alt="Friends" onerror="this.style.display='none'"> Invited Friends: <?php echo $count_ref; ?>
</div>

<a class="tophead" href="/ref.php?list=1">View List of Invited Friends</a>

<?php
foot($uid);
?>