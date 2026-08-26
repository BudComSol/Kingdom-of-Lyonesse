<?php
$location = true;
require_once __DIR__ . "/core/func.php";

head("Royal Tournament", $uid);
?>

<div class="tophead">
    <div class="ohh">
        <img class="img4" src="/images/adrian.png" width="50px" height="50px" alt="Adrian">
    </div>
    <span class="lime">Adrian (Tournament Master)</span><br>
    <small>
        The King has decreed a Royal Tournament! Earn tournament points through battles and daily tasks. The top 5 champions will receive generous royal rewards at the end of the week!
    </small>
</div>

<div class="list2">
    <small>
        <span style="float: left;">Hero</span>
        <span style="float: right;">Trophies / Points</span>
        <br>
    </small>
</div>

<?php
$user_q = $BaseConnect->query("SELECT * FROM `users` ORDER BY `tour` DESC, `id` ASC LIMIT 10");

$i = 1;
while ($users = $user_q->fetch(PDO::FETCH_ASSOC)) {
    $class = ($i % 2 == 0) ? 'list2' : 'list';
    echo '<div class="' . $class . '">';
    echo user_list($users['id']);
?>
    <span style="float: right;">
        <img src="/images/tour.png" width="16" height="16" alt="Trophy">
        <?php echo $users['tour']; ?>
    </span>
</div>
<?php
    $i++;
}
?>

<div class="tophead2 cntr">
    <small>
        <a href="/about/tour.php"><u>Tournament Rules & Rewards</u></a><br>
        Tournament resets every Sunday at 00:00 UTC
    </small>
</div>

<?php
foot($uid);
?>