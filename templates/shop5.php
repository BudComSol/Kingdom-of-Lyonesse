<?php
if (isset($_REQUEST['ok'])) {
    $res = abs(intval($_POST['res'] ?? 0));
    $coll = $res * 3;

    if (($user['gold'] ?? 0) < $coll) {
        ruby(null);
        header('Location: ?loc=5');
        exit();
    }

    if ($res <= 0) {
        err("Please enter a valid amount!");
        header('Location: ?loc=5');
        exit();
    }

    qry("UPDATE `users` SET `metal` = `metal` + ?, `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($res, $coll, $uid));
    msg("Purchased +{$res} Iron successfully!");
    header('Location: ?loc=5');
    exit();
}

if (isset($_REQUEST['ok2'])) {
    $res = abs(intval($_POST['res'] ?? 0));
    $coll = $res * 3;

    if (($user['gold'] ?? 0) < $coll) {
        ruby(null);
        header('Location: ?loc=5');
        exit();
    }

    if ($res <= 0) {
        err("Please enter a valid amount!");
        header('Location: ?loc=5');
        exit();
    }

    qry("UPDATE `users` SET `tkan` = `tkan` + ?, `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($res, $coll, $uid));
    msg("Purchased +{$res} Cloth successfully!");
    header('Location: ?loc=5');
    exit();
}

if (isset($_REQUEST['ok3'])) {
    $res = abs(intval($_POST['res'] ?? 0));
    $coll = $res * 3;

    if (($user['gold'] ?? 0) < $coll) {
        ruby(null);
        header('Location: ?loc=5');
        exit();
    }

    if ($res <= 0) {
        err("Please enter a valid amount!");
        header('Location: ?loc=5');
        exit();
    }

    qry("UPDATE `users` SET `kamen` = `kamen` + ?, `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($res, $coll, $uid));
    msg("Purchased +{$res} Stone successfully!");
    header('Location: ?loc=5');
    exit();
}

if (isset($_REQUEST['ok4'])) {
    $res = abs(intval($_POST['res'] ?? 0));
    $coll = $res * 3;

    if (($user['gold'] ?? 0) < $coll) {
        ruby(null);
        header('Location: ?loc=5');
        exit();
    }

    if ($res <= 0) {
        err("Please enter a valid amount!");
        header('Location: ?loc=5');
        exit();
    }

    qry("UPDATE `users` SET `swit` = `swit` + ?, `gold` = `gold` - ? WHERE `id` = ? LIMIT 1", array($res, $coll, $uid));
    msg("Purchased +{$res} Scrolls successfully!");
    header('Location: ?loc=5');
    exit();
}

head("Elizabeth - Resource Merchant", $uid);
?>

<div class="tophead">
    <div class="ohh"><img class="img4" src="/images/shop5.png" width="50px" height="50px" alt="Elizabeth"></div>
    <span class="lime">Elizabeth (Resource Merchant)</span><br>
    <small>Greetings traveler! I supply rare crafting materials for rubies. Pick what you need!</small>
</div>
<br>

<div class="tophead2 cntr">
    <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> <b>3 Rubies = 1 Resource Unit</b><br>
    Your Balance: <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> <b><?php echo $user['gold'] ?? 0; ?> Rubies</b>
</div>

<div class="tophead cntr">
    How much <img src="/images/metal.png" width="17px" height="17px" alt="Iron"> <b>Iron</b> do you want to buy?<br><br>
    <form action="" method="post">
        <input type="number" name="res" min="1" max="10000" required placeholder="Quantity" />
        <br><br>
        <input type="submit" name="ok" value="Buy Iron">
    </form>
</div>

<div class="tophead cntr">
    How much <img src="/images/tkan.png" width="17px" height="17px" alt="Cloth"> <b>Cloth</b> do you want to buy?<br><br>
    <form action="" method="post">
        <input type="number" name="res" min="1" max="10000" required placeholder="Quantity" />
        <br><br>
        <input type="submit" name="ok2" value="Buy Cloth">
    </form>
</div>

<div class="tophead cntr">
    How much <img src="/images/kamen.png" width="17px" height="17px" alt="Stone"> <b>Stone</b> do you want to buy?<br><br>
    <form action="" method="post">
        <input type="number" name="res" min="1" max="10000" required placeholder="Quantity" />
        <br><br>
        <input type="submit" name="ok3" value="Buy Stone">
    </form>
</div>

<div class="tophead cntr">
    How many <img src="/images/swit.png" width="17px" height="17px" alt="Scrolls"> <b>Scrolls</b> do you want to buy?<br><br>
    <form action="" method="post">
        <input type="number" name="res" min="1" max="10000" required placeholder="Quantity" />
        <br><br>
        <input type="submit" name="ok4" value="Buy Scrolls">
    </form>
</div>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>