<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (isset($_REQUEST['ok'])) {
    $ruby = abs(intval($_POST['ruby'] ?? 0));
    $coll = $ruby * 1000;

    if (($user['gold'] ?? 0) < $ruby) {
        ruby(null);
        header('Location: /gold.php');
        exit();
    }

    if ($ruby <= 0) {
        err("Please enter a valid amount of rubies!");
        header('Location: /gold.php');
        exit();
    }

    qry("UPDATE `users` SET `silver` = `silver` + ?, `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($coll, $ruby, $uid));
    msg("Currency exchanged successfully! (+{$coll} Gold Coins)");
    header('Location: /gold.php');
    exit();
}

head("Currency Exchange", $uid);
?>

<div class="tophead cntr">
    <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> 1 Ruby = <img src="/images/gold.png" width="17px" height="17px" alt="Gold"> 1,000 Gold Coins<br>
    Your Balance: <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> <?php echo $user['gold'] ?? 0; ?> Rubies
</div>

<div class="tophead cntr">
    <?php echo logs(); ?>
    How many rubies do you want to exchange?
    <br><br>
    <form action="" method="post">
        <input type="number" name="ruby" min="1" max="<?php echo (int)($user['gold'] ?? 0); ?>" required />
        <br><br>
        <input type="submit" name="ok" value="Exchange Currency">
    </form>
</div>

<a href="/shop.php" class="tophead">Back to Town / Shops</a>

<?php
foot($uid);
?>